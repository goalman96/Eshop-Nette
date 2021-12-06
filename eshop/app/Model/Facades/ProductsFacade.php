<?php

namespace App\Model\Facades;

use App\Model\Entities\Product;
use App\Model\Repositories\ProductRepository;
use http\Exception;
use Nette\Utils\Strings;

class ProductsFacade {

    /** @var ProductRepository $productRepository */
    private $productRepository;

    public function __construct(ProductRepository $productRepository) {
        $this->productRepository = $productRepository;
    }

    /**
     * Metóda pre získanie jedného konkrétneho produktu na základe jeho id
     * @param int $id
     * @return Product
     * @throws \Exception
     */

    public function getProductById(int $id):Product {
        return $this->productRepository->find($id);
    }

    /**
     * Metóda pre získanie jedného konkrétneho produktu na základe jeho jedinečnej url adresy
     * @param string $url
     * @return Product
     * @throws \Exception
     */

    public function getProductByUrl(string $url):Product {
        return $this->productRepository->findBy(['url'=>$url]);
    }

    /**
     * Metóda na vyhľadávanie produktov
     * @param array|null $params
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */

    public function findProducts(array $params=null, int $limit=null, int $offset=null):array {
        return $this->productRepository->findAllBy($params, $offset, $limit);
    }

    /**
     * Metóda pre uloženie objektu
     * @param Product $product
     */

    public function saveProduct(Product $product) {
        if(empty($product->url)) {
            $baseUrl=Strings::webalize($product->title);
        } else {
            $baseUrl=$product->title;
        }

        $urlNumber=1;
        $url=$baseUrl;
        $productId= isset($product->productId) ? $product->productId : null;

        try {
            while($existingProduct = $this->getProductByUrl($url)) {
                if($existingProduct->productId=$productId) {
                    $product->url=$url;
                    break;
                }
                $urlNumber++;
                $url=$baseUrl.$urlNumber;
            }
        } catch (\Exception $exception) {

        }

        $product->url=$url;

        $this->productRepository->persist($product);
    }

    /**
     * Metóda na uloženie fotky produktu
     * @param FileUpload $fileUpload
     * @param Product $product
     */

    public function saveProductPhoto(FileUpload $fileUpload, Product &$product) {
        if ($fileUpload->isOk() && $fileUpload->isImage()){
            $fileExtension=strtolower($fileUpload->getImageFileExtension());
            $fileUpload->move(__DIR__.'/../../../www/img/products/'.$product->productId.'.'.$fileExtension);
            $product->photoExtension=$fileExtension;
            $this->saveProduct($product);
        }
    }

}