<?php

namespace App\Singleton;

use App\Utils\ProductNew;
use App\Utils\ProductTopSale;

class ProductListID {
    static private $_instance = NULL;
    static function getInstance($store_id) {
      if (self::$_instance == NULL) {
        self::$_instance = new ProductListID();

        self::$_instance->list_id_new = ProductNew::getNewProductIds($store_id);
        self::$_instance->list_id_top_sale = ProductTopSale::getTopSaleProductIds($store_id);
      }
      return self::$_instance;
    }


    public $list_id_new = [];
    public $list_id_top_sale = [];

  }
