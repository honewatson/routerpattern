<?php
/**
 */


ini_set('display_errors', 1);
include "../api/autoload.php";
include "../api/errors/basic.php";

$basepath = dirname(__file__) . '/../';
$autoload = new api\autoload($basepath, new api\errors\basic);
$autoload->autoload();

$request = new api\request(
        $_SERVER,
        $_GET,
        $_POST,
        $_COOKIE,
        $_FILES,
        $_ENV
);
class repository_factory {
    public function __construct($repository, $request, $repository_factories, $view, $response, $basic) {
        $this->repository = $repository;
        $this->request = $request;
        $this->repository_factories = $repository_factories;
        $this->view = $view;
        $this->response = $response;
        $this->basic = $basic;
    }

    public function build(){
        $factory_name = $this->repository->findOne($this->repository_factories);
        $factory = new $factory_name->class($this->repository, $this->request, $this->view->build(), $this->response->build($factory_name->response));
        /**
         *  This factory will create a subject which adds model observers
         *  $class->addModel( new xmodel );
         *  $class->addModel( new ymodel );
         *  $class->addModel( new zmodel );
         *
         *  $class->getVars = function () {
         *      foreach($this->models as $model)
         *          $model->getVars($this);
         *  }
         *  $model->getVars($subject) = function () {
         *      // do stuff to get $results;
         *      $this->param = $subject->xmodel->param;
         *      $name = $this->name;
         *      $subject->$name = $results;
         *  }
         *
         *
         *  $class->main = function () {
         *      $view = $this->view->getString($this->getVars());
         *      $this->response->setView($view);
         *      $this->response->send();
         *
         *  }
         *
         *
         */
        $factory->build();
    }
}
$pageControllerFactory = new repository_factory(
    new api\repository(
        $pdo,
        new api\errors\mysql
    ),
    $request,
    new api\repository\factories($request),
    new api\view\factory,
    new api\response\factory,
    new api\errors\basic
);

$pageController = $pageControllerFactory->build();

$pageController->main();