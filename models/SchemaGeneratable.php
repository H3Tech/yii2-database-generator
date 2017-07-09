<?php

namespace h3tech\databaseGenerator\models;

interface SchemaGeneratable
{
    /**
     * @return array
     */
    public static function fieldTypes();
}
