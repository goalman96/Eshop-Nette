<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Components\UserEditForm\UserEditForm;
use App\AdminModule\Components\UserEditForm\UserEditFormFactory;
use App\Model\Facades\UsersFacade;

class UserPresenter extends BasePresenter{
    /** @var UsersFacade $usersFacade */
    private $usersFacade;
    /** @var UserEditFormFactory $userEditFormFactory */
    private $userEditFormFactory;

    /**
     * Akce pro vykreslení seznamu uživatelů
     */
    public function renderDefault():void {
        $this->template->users=$this->usersFacade->findUsers(['order'=>'name']);
    }

    /**
     * Akce pro úpravu jednoho uživatele
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
    public function renderEdit(int $id):void {
        try{
            $usr=$this->usersFacade->getUser($id);
        }catch (\Exception $e){
            $this->flashMessage('Uživatel nebyl nalezen.', 'error');
            $this->redirect('default');
        }
        $form=$this->getComponent('userEditForm');
        $form->setDefaults($usr);
        $this->template->usr=$usr;
    }

    /**
     * Akce pro smazání uživatele
     * @param int $id
     * @throws \Nette\Application\AbortException
     */
    public function actionDelete(int $id):void {
        try{
            $usr=$this->usersFacade->getUser($id);
        }catch (\Exception $e){
            $this->flashMessage('Uživatel nebyl nalezen.', 'error');
            $this->redirect('default');
        }

        if ($this->usersFacade->deleteUser($usr)){
            $this->flashMessage('Uživatel byl smazán.', 'info');
        }else{
            $this->flashMessage('Uživatele není možné smazat.', 'error');
        }

        $this->redirect('default');
    }

    /**
     * Formulář na editaci uživatelů
     * @return UserEditForm
     */
    public function createComponentUserEditForm():UserEditForm {
        $form = $this->userEditFormFactory->create();
        $form->onCancel[]=function(){
            $this->redirect('default');
        };
        $form->onFinished[]=function($message=null){
            if (!empty($message)){
                $this->flashMessage($message);
            }
            $this->redirect('default');
        };
        $form->onFailed[]=function($message=null){
            if (!empty($message)){
                $this->flashMessage($message,'error');
            }
            $this->redirect('default');
        };
        return $form;
    }

    #region injections
    public function injectUsersFacade(UsersFacade $usersFacade){
        $this->usersFacade=$usersFacade;
    }
    public function injectUserEditFormFactory(UserEditFormFactory $userEditFormFactory){
        $this->userEditFormFactory=$userEditFormFactory;
    }
    #endregion injections

}
