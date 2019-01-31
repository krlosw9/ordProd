<?php

namespace App\Models;

class Project extends BaseElement{
	public $title;
	public $descripcion;

	public function __construct($title,$descripcion){
		$this->title=$title;
		$this->descripcion=$descripcion;
	}
}