<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Components\ProductEditForm\ProductEditForm;
use App\AdminModule\Components\ProductEditForm\ProductEditFormFactory;
use App\Model\Facades\ProductsFacade;

class ProductPresenter extends BasePresenter {
    /** @var ProductsFacade $productsFacade */
    private $productsFacade;

    /** @var ProductEditFormFactory $productEditFormFactory */
    private $productEditFormFactory;

    public function __construct(ProductsFacade $productsFacade, ProductEditFormFactory $productEditFormFactory) {
        $this->productsFacade=$productsFacade;
        $this->productEditFormFactory=$productEditFormFactory;
    }

    public function createComponentProductEditForm():ProductEditForm {
        $form = $this->productEditFormFactory->create();
        $form->onCancel[]=function(){
            $this->redirect('Dashboard');
        };
        $form->onFinished[]=function($message=null){
            if (!empty($message)){
                $this->flashMessage($message);
            }
            $this->redirect('Dashboard');
        };
        $form->onFailed[]=function($message=null){
            if (!empty($message)){
                $this->flashMessage($message,'error');
            }
            $this->redirect('Dashboard');
        };
        return $form;
    }
}