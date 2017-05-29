<?php

namespace App;

use App\Entity\Game;
use App\Entity\Season;
use App\Entity\User;
use App\Handler\GameHandler;
use Silex\Api\ControllerProviderInterface;
use Silex\Application as App;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

class ControllerProvider implements ControllerProviderInterface
{
    private $app;

    public function connect(App $app)
    {
        $this->app = $app;

        $app->error([$this, 'error']);

        $controllers = $app['controllers_factory'];

        $controllers
            ->get('/', [$this, 'homepage'])
            ->bind('homepage');

        $controllers
            ->get('/add_season', [$this, 'addSeason'])
            ->bind('add_season');

        $controllers
            ->get('/add_users', [$this, 'addUsers'])
            ->bind('add_users');

        $controllers
            ->get('/generate_games', [$this, 'generateGames'])
            ->bind('generate_games');

        $controllers
            ->match('/make_score', [$this, 'makeScore'])
            ->bind('make_score');

        $controllers
            ->match('/get_results', [$this, 'getResults'])
            ->bind('get_results');

        return $controllers;
    }

    public function getResults(App $app, Request $request)
    {
        $order = array();
        $orderBy = $request->get('order_by');
        if (null !== $request->get('order_by')) {
            $order = array('score' => ($orderBy = ($request->get('order_by') == 'ASC' ? 'DESC' : 'ASC')));
        }

        $gamesData = $app['repository.game']->findGamesForSeason(
            ($app['session']->has('season_id') ?
            $app['session']->get('season_id') : null),
            $order
        );
        return $app['twig']->render('get_results.html.twig', array(
            'gamesData' => $gamesData, 'order_by' => $orderBy
        ));
    }

    public function makeScore(App $app, Request $request)
    {
        $builder = $app['form.factory']->createBuilder(Type\FormType::class);
        $builder
            ->setMethod('POST');

        $sUsers = $app['session']->get('users');
        foreach ($app['session']->get('games') AS $k => $game) {
           $choices = array($sUsers[$game[0][0]].' & '.$sUsers[$game[0][1]] => 0 ,
               $sUsers[$game[1][0]].' & '.$sUsers[$game[1][1]] => 1 );
           $builder->add('choice_'.$k.'', Type\ChoiceType::class, array(
               'constraints' => array(new Assert\NotNull()),
               'choices' => $choices,
                'multiple' => false,
                'expanded' => true,
               'label' => 'Game '.$k
            ));
        }
        $builder
            ->add('submit', Type\SubmitType::class, array('label' => 'Save games'));

        $form = $builder->getForm();

        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                $app['db']->beginTransaction();
                try {
                    $formData = ($request->get('form'));
                    $games = $app['session']->get('games');
                    //var_dump($games); die();
                    foreach ($formData AS $k => $v) {
                        $kList = explode('_', $k);
                        if (array_key_exists(1, $kList)) {
                            $game = new Game();
                            $game->setCreatedAt(new \DateTime('now'));
                            $game->setSeasonId($app['session']->get('season_id'));
                            $app['repository.game']->save($game);

                            $app['db']->insert('user_game',
                                array('user_id' => $games[$kList[1]][0][0], 'game_id' => $game->getId(),
                                    'score' => ($v == 0 ? 1 : 0)));
                            $app['db']->insert('user_game',
                                array('user_id' => $games[$kList[1]][0][1], 'game_id' => $game->getId(),
                                    'score' => ($v == 0 ? 1 : 0)));

                            $app['db']->insert('user_game',
                                array('user_id' => $games[$kList[1]][1][0], 'game_id' => $game->getId(),
                                    'score' => ($v == 1 ? 1 : 0)));
                            $app['db']->insert('user_game',
                                array('user_id' => $games[$kList[1]][1][1], 'game_id' => $game->getId(),
                                    'score' => ($v == 1 ? 1 : 0)));
                        }
                    }
                    $app['db']->commit();
                } catch (\Exception $e) {
                    $app['db']->rollBack();
                    throw $e;
                }
                $redirect = $app['url_generator']->generate('get_results');
                return $app->redirect($redirect);
            }
        }

        return $app['twig']->render('make_scores_form.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function generateGames(App $app, Request $request)
    {
        $builder = $app['form.factory']->createBuilder(Type\FormType::class);
        $form = $builder
            ->setMethod('GET')
            ->add('submit', Type\SubmitType::class, array('label' => 'Generate games'))
            ->getForm();

        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                $userList = array();
                $users = $app['session']->get('users');
                foreach ($users AS $key => $user) {
                    $userList[] = $key;
                }
                $gameHandler = new GameHandler($userList);
                $games = $gameHandler->makeGamesForPlayersNo();
                $app['session']->set('games', $games);

                $redirect = $app['url_generator']->generate('make_score');
                return $app->redirect($redirect);
            }
        }

        return $app['twig']->render('generate_games_form.html.twig', array(
            'form' => $form->createView(),
        ));

    }

    public function addUsers(App $app, Request $request)
    {
        $userNo = $app['session']->get('user_no');
        $builder = $app['form.factory']->createBuilder(Type\FormType::class);
        for ($i = 0; $i < $userNo; $i++) {
            $builder
                ->add('user_'.$i, Type\TextType::class, array(
                    'constraints' => new Assert\NotBlank(),
                    'attr' => array('placeholder' => 'not blank constraints'),
                ));
        }
        $form = $builder
            ->setMethod('GET')
            ->add('submit', Type\SubmitType::class)
            ->getForm();

        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                $toSession = array();
                $data = $form->getData();
                foreach ($data as $nick) {
                    $user = new User();
                    $user->setNickname($nick);
                    $app['repository.user']->save($user);
                    $toSession[$user->getId()] = $user->getNickname();
                }
                $app['session']->set('users',  $toSession);

                $redirect = $app['url_generator']->generate('generate_games');
                return $app->redirect($redirect);
            }
        }

        return $app['twig']->render('add_users_form.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function addSeason(App $app, Request $request)
    {
        $builder = $app['form.factory']->createBuilder(Type\FormType::class);
        $choices = array(4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10);
        $form = $builder
            ->setMethod('GET')
            //->setAction($this->generateUrl('add_users'))
            ->add('user_no', Type\ChoiceType::class, array(
                'choices' => $choices,
                'multiple' => false,
                'expanded' => false,
                'label' => 'Select player number'
            ))
            ->add('submit', Type\SubmitType::class)
            ->getForm();

        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                $season = new Season();
                $season->setCreatedAt(new \DateTime('now'));
                $data = $form->getData();
                $app['repository.season']->save($season);
                $app['session']->set('season_id', $season->getId());
                $app['session']->set('user_no', $data['user_no']);

                $redirect = $app['url_generator']->generate('add_users');
                return $app->redirect($redirect);
            } else {
                $form->addError(new FormError('This is a global error'));
            }
        }

        return $app['twig']->render('add_season_form.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function homepage(App $app)
    {
        $app['session']->getFlashBag()->add('warning', 'Warning flash message');
        $app['session']->getFlashBag()->add('info', 'Info flash message');
        $app['session']->getFlashBag()->add('success', 'Success flash message');
        $app['session']->getFlashBag()->add('danger', 'Danger flash message');

        return $app['twig']->render('index.html.twig');
    }

    public function error(\Exception $e, Request $request, $code)
    {
        if ($this->app['debug']) {
            return;
        }

        switch ($code) {
            case 404:
                $message = 'The requested page could not be found.';
                break;
            default:
                $message = 'We are sorry, but something went terribly wrong.';
        }

        return new Response($message, $code);
    }
}
