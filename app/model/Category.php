<?php

class Category extends TRecord
{
    const TABLENAME = 'category';
    const PRIMARYKEY = 'id';
    const IDPOLICY = 'serial'; // ou 'manual' dependendo do seu setup
}
?>