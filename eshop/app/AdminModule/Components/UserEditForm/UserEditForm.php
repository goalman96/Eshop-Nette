<?php

namespace App\AdminModule\Components\UserEditForm;

use App\Model\Entities\Role;
use App\Model\Entities\User;
use App\Model\Facades\RolesFacade;
use App\Model\Facades\UsersFacade;
use Nette;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\SmartObject;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;

/**
 * Class UserEditForm
 * @package App\AdminModule\Components\UserEditForm
 *
 * @method onFinished(string $message = '')
 * @method onFailed(string $message = '')
 * @method onCancel()
 */
class UserEditForm extends Form{

    use SmartObject;

    /** @var callable[] $onFinished */
    public $onFinished = [];
    /** @var callable[] $onFailed */
    public $onFailed = [];
    /** @var callable[] $onCancel */
    public $onCancel = [];
    /** @var RolesFacade $rolesFacade */
    private $rolesFacade;
    /** @var UsersFacade $usersFacade */
    private $usersFacade;
    /** @var Nette\Security\Passwords $passwords */
    private $passwords;

    /**
     * UserEditForm constructor.
     * @param Nette\ComponentModel\IContainer|null $parent
     * @param string|null $name
     * @param UsersFacade $usersFacade
     * @noinspection PhpOptionalBeforeRequiredParametersInspection
     */
    public function __construct(Nette\ComponentModel\IContainer $parent = null, string $name = null, RolesFacade $rolesFacade, UsersFacade $usersFacade, Nette\Security\Passwords $passwords){
        parent::__construct($parent, $name);
        $this->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));
        $this->rolesFacade=$rolesFacade;
        $this->usersFacade=$usersFacade;
        $this->passwords = $passwords;
        $this->createSubcomponents();
    }

    private function createSubcomponents(){
        $userId=$this->addHidden('userId');
        $this->addText('name','Jméno a příjmení:')
            ->setRequired('Zadejte své jméno')
            ->setHtmlAttribute('maxlength',40)
            ->addRule(Form::MAX_LENGTH,'Jméno je příliš dlouhé, může mít maximálně 40 znaků.',40);
        $this->addEmail('email','E-mail')
            ->setRequired('Zadejte platný email');
        $this['email']->addRule(function (Nette\Forms\Controls\TextInput $input) {
            try {
                $this->usersFacade->getUserByEmail($input->value);
            } catch (\Exception $e) {
                //pokud nebyl uživatel nalezen (tj. je vyhozena výjimka), je to z hlediska registrace v pořádku
                return true;
            }
            return false;
        }, 'Uživatel s tímto e-mailem je již v databázi.');
        $password=$this->addPassword('password','Heslo');
        $password
            ->setRequired('Zadejte požadované heslo')
            ->addRule(Form::MIN_LENGTH,'Heslo musí obsahovat minimálně 5 znaků.',5);
        $this->addPassword('password2','Heslo znovu:')
            ->addRule(Form::EQUAL,'Hesla se neshodují',$password);
        #region role
        $roles=$this->rolesFacade->findRoles();
        $rolesArr=[];
        foreach ($roles as $role){
            $rolesArr[$role->roleId]=$role->roleId;
        }
        $this->addSelect('roleId','Role',$rolesArr)
            ->setPrompt('--vyberte roli--')
            ->setRequired('Vyberte roli');
        #endregion role
        $this->addText('facebookId','Facebook ID:')
            ->setHtmlAttribute('length',15)
            ->addRule(Form::LENGTH,'Facebook ID musí mít 15 znaků.',15);
        $this->addSubmit('ok','uložit')
            ->onClick[]=function(SubmitButton $button){
            $values=$this->getValues('array');
            if (!empty($values['userId'])){
                try{
                    $usr=$this->usersFacade->getUser($values['userId']);
                }catch (\Exception $e){
                    $this->onFailed('Uživatel nebyl nalezen.');
                    return;
                }
            }else{
                $usr=new User();
                $usr->password=$this->passwords->hash($values['password']);
            }
            $usr->assign($values,['name','email', 'facebookId']);
            $role = $this->usersFacade->
            $this->usersFacade->saveUser($usr);
            $this->setValues(['userId'=>$usr->userId]);
            $this->onFinished('Uživatel byl uložen.');
        };
        $this->addSubmit('storno','zrušit')
            ->setValidationScope([$userId])
            ->onClick[]=function(SubmitButton $button){
            $this->onCancel();
        };
    }

    /**
     * Metoda pro nastavení výchozích hodnot formuláře
     * @param array|object $values
     * @param bool $erase
     * @return $this
     */

    public function setDefaults($values, bool $erase = false):self {
        if ($values instanceof User){
            $values = [
                'userId'=>$values->userId,
                'name'=>$values->name,
                'email'=>$values->email,
                'roleId'=>$values->roleId,
                'facebookId'=>$values->facebookId
            ];
        }
        parent::setDefaults($values, $erase);
        return $this;
    }
}
