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

    public function renderEdit(int $id):void {
        try {
            $product = $this->productsFacade->getProductById($id);
        } catch (\Exception $e) {
            $this->flashMessage('Požadovaný produkt nebyl nalezen.', 'error');
            $this->redirect('Dashboard:default');
        }
        $form=$this->getComponent('productEditForm');
        $form->setDefaults($product);
        $this->template->product=$product;
    }


    public function createComponentProductEditForm():ProductEditForm {
        $form = $this->productEditFormFactory->create();
        $form->onCancel[]=function(){
            $this->redirect('Dashboard:default');
        };
        $form->onFinished[]=function($message=null){
            if (!empty($message)){
                $this->flashMessage($message);
            }
            $this->redirect('Dashboard:default');
        };
        $form->onFailed[]=function($message=null){
            if (!empty($message)){
                $this->flashMessage($message,'error');
            }
            $this->redirect('Dashboard:default');
        };
        return $form;
    }
}