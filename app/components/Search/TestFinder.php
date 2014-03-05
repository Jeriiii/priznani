<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TestFinder
 *
 * @author Mario
 */
class TestFinder extends Nette\Object
{	
        private $table;

        public function __construct(\Nette\Database\Table\Selection $table)
        {
            $this->table = $table;
        }
            /*
             * vrati celeho uzivatele z databaze
             */
            public function getUsersFromDB()
            {
                return $this->table->limit(8);
            }
            
            public function getAllUsersFromDB()
            {
                return $this->table;
            }
}
