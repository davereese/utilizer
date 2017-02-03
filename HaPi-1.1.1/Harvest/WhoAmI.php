<?php
/*
 * copyright (c) 2009 MDBitz - Matthew John Denton - mdbitz.com
 *
 * This file is part of HarvestAPI.
 *
 * HarvestAPI is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * HarvestAPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with HarvestAPI. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * User
 *
 * This file contains the class Harvest_User
 *
 * @author Matthew John Denton <matt@mdbitz.com>
 * @package com.mdbitz.harvest
 */

/**
 * Harvest WhoAmI Object
 *
 * <b>Properties</b>
 * <ul>
 *   <li>tbd</li>
 * </ul>
 *
 * @package com.mdbitz.harvest
 */
class Harvest_WhoAmI extends Harvest_Abstract {

    /**
     * @var string user
     */
    protected $_root = "textContent";

    /**
       * parse XML represenation into a Harvest Project object
       *
       * @param XMLNode $node xml node to parse
       * @return void
       */
    public function parseXML( $node ) {
  		foreach ( $node->childNodes as $item ) {
  			switch( $item->nodeName )
  			{
  				case "user":
  					$this->_user = $this->parseNode( $item );
  				break;
  				default:
  					if( $item->nodeName != "#text" ){
  						$this->set( $item->nodeName, $item->nodeValue);
  					}
  				break;
  			}
  		}

  	}

  	/**
  	 * parse xml node
  	 * @param XMLNode $node
  	 * @return mixed
  	 */
  	private function parseNode( $node ) {
  		$item = null;

  		switch( $node->nodeName )
  		{
  			case "user":
  				$item = new Harvest_User();
  			break;
  			default:
  			break;
  		}
      //var_dump($node->nodeName);
  		if( ! is_null( $item ) ) {
  			$item->parseXML( $node );
  		}

  		return $item;

  	}

}
