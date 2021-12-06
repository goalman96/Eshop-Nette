<?php

namespace App\AdminModule\Components\ProductEditForm;

use App\Model\Facades\CategoriesFacade;
use App\Model\Facades\ProductsFacade;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\SmartObject;
use Nette\Utils\Strings;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;

/**
 *
 */

class ProductEditForm extends Form {

    use SmartObject;

    /** @var callable[] $onFinished */
    public $onFinished = [];
    /** @var callable[] $onFailed */
    public $onFailed = [];
    /** @var callable[] $onCancel */
    public $onCancel = [];
    /** @var CategoriesFacade $categoriesFacade */
    private $categoriesFacade;
    /** @var ProductsFacade $productsFacade */
    private $productsFacade;


    /**
     * @param Nette\ComponentModel\IContainer|null $parent
     * @param string|null $name
     * @param CategoriesFacade $categoriesFacade
     * @param ProductsFacade $productsFacade
     */

    public function __construct(Nette\ComponentModel\IContainer $parent = null, string $name = null, CategoriesFacade $categoriesFacade, ProductsFacade $productsFacade)
    {
        parent::__construct($parent, $name);
        $this->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));
        $this->categoriesFacade=$categoriesFacade;
        $this->productsFacade=$productsFacade;
    }

    /**
     * Metóda pre vytvorenie komponenty na editáciu produktu
     */

    public function createSubComponents() {
        $productId=$this->addHidden('productId');
        $this->addText('title', 'Název produktu')
            ->setRequired(true)
            ->setMaxLength(100);
        $this->addText('url', 'URL produktu')
            ->setMaxLength(100)
            ->addFilter(function (string $url) {
                    return Strings::webalize($url);
                }
            )->addRule(function(Nette\Forms\Controls\TextInput $input)use($productId){
                try{
                    $existingProduct = $this->productsFacade->getProductByUrl($input->value);
                    return $existingProduct->productId==$productId->value;
                }catch (\Exception $e){
                    return true;
                }
            },'Zvolená URL je již obsazena jiným produktem');

        $categories=$this->categoriesFacade->findCategories();
        $categoriesArr=[];
        foreach ($categories as $category){
            $categoriesArr[$category->categoryId]=$category->title;
        }
        $this->addSelect('categoryId','Kategorie',$categoriesArr)
            ->setPrompt('--vyberte kategorii--')
            ->setRequired(false);

        $this->addTextArea('description', 'Popis produktu')
            ->setRequired('Zadejte popis produktu.');

        $this->addText('price', 'Cena')
            ->setHtmlType('number')
            ->addRule(Form::NUMERIC)
            ->setRequired('Musíte zadat cenu produktu');//tady by mohly být další kontroly pro min, max atp.

        $this->addCheckbox('available', 'Nabízeno ke koupi')
            ->setDefaultValue(true);

        #region obrázek
        $photoUpload=$this->addUpload('photo','Fotka produktu');
        //pokud není zadané ID produktu, je nahrání fotky povinné
        $photoUpload //vyžadování nahrání souboru, pokud není známé productId
        ->addConditionOn($productId, Form::EQUAL, '')
            ->setRequired('Pro uložení nového produktu je nutné nahrát jeho fotku.');

        $photoUpload //limit pro velikost nahrávaného souboru
        ->addRule(Form::MAX_FILE_SIZE, 'Nahraný soubor je příliš velký', 1000000);

        $photoUpload //kontrola typu nahraného souboru, pokud je nahraný
        ->addCondition(Form::FILLED)
            ->addRule(function(Nette\Forms\Controls\UploadControl $photoUpload){
                $uploadedFile = $photoUpload->value;
                if ($uploadedFile instanceof Nette\Http\FileUpload){
                    $extension=strtolower($uploadedFile->getImageFileExtension());
                    return in_array($extension,['jpg','jpeg','png']);
                }
                return false;
            },'Je nutné nahrát obrázek ve formátu JPEG či PNG.');
        #endregion obrázek

        $this->addSubmit('ok','uložit')
            ->onClick[]=function(SubmitButton $button){
            $values=$this->getValues('array');
            if (!empty($values['productId'])){
                try{
                    $product=$this->productsFacade->getProduct($values['productId']);
                }catch (\Exception $e){
                    $this->onFailed('Požadovaný produkt nebyl nalezen.');
                    return;
                }
            }else{
                $product=new Product();
            }
            $product->assign($values,['title','url','description','available']);
            $product->price=floatval($values['price']);
            $this->productsFacade->saveProduct($product);
            $this->setValues(['productId'=>$product->productId]);

            //uložení fotky
            if (($values['photo'] instanceof Nette\Http\FileUpload) && ($values['photo']->isOk())){
                try{
                    $this->productsFacade->saveProductPhoto($values['photo'], $product);
                }catch (\Exception $e){
                    $this->onFailed('Produkt byl uložen, ale nepodařilo se uložit jeho fotku.');
                }
            }

            $this->onFinished('Produkt byl uložen.');
        };
        $this->addSubmit('storno','zrušit')
            ->setValidationScope([$productId])
            ->onClick[]=function(SubmitButton $button){
            $this->onCancel();
        };
    }

    /**
     * Metóda, ktorá nastaví východzie hodnoty z formulára
     * @param array|object $values
     * @param bool $erase
     * @return $this
     */

    public function setDefaults($values, bool $erase = false):self {
        if ($values instanceof Product){
            $values = [
                'productId'=>$values->productId,
                'categoryId'=>$values->category?$values->category->categoryId:null,
                'title'=>$values->title,
                'url'=>$values->url,
                'description'=>$values->description,
                'price'=>$values->price
            ];
        }
        parent::setDefaults($values, $erase);
        return $this;
    }

}