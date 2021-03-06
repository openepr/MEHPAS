<?php
/**
 * (C) OpenEyes Foundation, 2014
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (C) 2014, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

class PasTransformerTest extends CDbTestCase
{
	public $fixtures = array(
		'AddressType',
		'Country',
	);

	public function fixCaseDataProvider()
	{
		return array(
			array("FOO BAR", "Foo Bar"),
			array("FOO-BAR", "Foo-Bar"),
			array("FOO'BAR", "Foo'Bar"),
			array("FOO.BAR", "Foo.Bar"),
			array("FOO'S BAR", "Foo's Bar"),
		);
	}

	/**
	 * @dataProvider fixCaseDataProvider
	 */
	public function testFixCase($input, $expected)
	{
		$this->assertEquals($expected, PasTransformer::fixCase($input));
	}

	public function parseAddressProvider()
	{
		return array(
			array(  // Basic empty address
				array(
				),
				array(
				),
			),
			array(  // Duplicate property name and number
				array(
					'PROPERTY_NAME' => '10',
					'PROPERTY_NO' => '10',
				),
				array(
					'address1' => '10',
				),
			),
			array(  // Property no appears in addr1
				array(
					'PROPERTY_NO' => '10',
					'ADDR1' => '10, TEST STREET',
				),
				array(
					'address1' => '10 Test Street',
				),
			),
			array(  // Property name appears in addr1
				array(
					'PROPERTY_NAME' => 'FRED',
					'ADDR1' => 'FRED, TEST STREET',
				),
				array(
					'address1' => "Fred\nTest Street",
				),
			),
			array(  // Combine property name, number and addr1
				array(
					'PROPERTY_NAME' => 'FRED',
					'PROPERTY_NO' => '10',
					'ADDR1' => 'TEST STREET',
				),
				array(
					'address1' => "Fred\n10 Test Street",
				),
			),
			array(  // UK specified
				array(
					'ADDR2' => 'UNITED KINGDOM',
				),
				array(
				),
			),
			array(  // Postcode in correct field
				array(
					'POSTCODE' => 'EC1V 2PD',
				),
				array(
					'postcode' => 'EC1V 2PD',
				),
			),
			array(  // Postcode in another field
				array(
					'ADDR3' => 'EC1V 2PD',
				),
				array(
					'postcode' => 'EC1V 2PD',
				),
			),
			array(  // Non-uk country specified
				array(
					'ADDR2' => 'CANADA',
				),
				array(
					'country' => 'Canada',
				),
			),
			array(  // Non-uk country line extraction
				array(
					'ADDR1' => 'ADDR1',
					'ADDR2' => 'ADDR2',
					'ADDR3' => 'ADDR3',
					'ADDR4' => 'ADDR4',
					'ADDR5' => 'CANADA',
				),
				array(
					'address1' => 'Addr1',
					'address2' => 'Addr2',
					'city' => 'Addr3',
					'county' => 'Addr4',
					'country' => 'Canada',
				),
			),
			array(  // Address type lookup
				array(
					'ADDR_TYPE' => 'H',
				),
				array(
					'address_type' => 'Home',
				),
			),
			array(
				array(
					'ADDR1' => '52 ROSSLYN ROAD',
					'ADDR2' => 'CHESTER',
					'ADDR3' => 'HERTFORD',
					'POSTCODE' => 'WD6 3AH',
				),
				array(
					'address1' => '52 Rosslyn Road',
					'city' => 'Chester',
					'county' => 'Hertford',
					'postcode' => 'WD6 3AH',
				),
			),
			array(
				array(
					'ADDR1' => '25 HENRY STREET',
					'ADDR2' => 'REDHALL LA',
					'ADDR3' => 'HARROW',
					'ADDR4' => 'MIDDX',
				),
				array(
					'address1' => '25 Henry Street',
					'address2' => 'Redhall La',
					'city' => 'Harrow',
					'county' => 'Middx',
				),
			),
			array(
				array(
					'ADDR1' => '43 MANOR ROAD',
					'ADDR2' => 'SOUTHALL',
					'ADDR3' => 'MIDDLESEX',
					'POSTCODE' => 'UB1 8EG',
				),
				array(
					'address1' => '43 Manor Road',
					'city' => 'Southall',
					'county' => 'Middlesex',
					'postcode' => 'UB1 8EG',
				),
			),
			array(
				array(
					'ADDR1' => '11 DANE COURT',
					'ADDR2' => 'ALDERWOOD COURT',
					'ADDR3' => 'BRIXTON',
					'ADDR4' => 'LONDON',
					'POSTCODE' => 'SW2 3AH',
				),
				array(
					'address1' => '11 Dane Court',
					'address2' => 'Alderwood Court',
					'city' => 'Brixton',
					'county' => 'London',
					'postcode' => 'SW2 3AH',
				),
			),
			array(
				array(
					'ADDR1' => 'FLAT 4',
					'ADDR2' => '10 FOO STREET',
					'ADDR3' => 'BAR CRESCENT',
					'ADDR4' => 'ISLINGTON',
					'ADDR5' => 'LONDON',
					'POSTCODE' => 'N4 5AZ',
				),
				array(
					'address1' => 'Flat 4',
					'address2' => "10 Foo Street\nBar Crescent",
					'city' => 'Islington',
					'county' => 'London',
					'postcode' => 'N4 5AZ',
				),
			),
			// Odd postcode
			array(
				array(
					'ADDR1' => '42 FOO STREET',
					'POSTCODE' => 'BLERGH ARGH 123',
				),
				array(
					'address1' => '42 Foo Street',
					'postcode' => 'BLERGH ARG',
				),
			),
			// County in the postcode field
			array(
				array(
					'ADDR1' => '11 SOME STREET',
					'ADDR2' => 'SOME OTHER BIT OF AN ADDRESS',
					'ADDR3' => 'CANTERBURY',
					'POSTCODE' => 'KENT'
				),
				array(
					'address1' => '11 Some Street',
					'address2' => 'Some Other Bit Of An Address',
					'city' => 'Canterbury',
					'county' => 'Kent',
					'postcode' => 'KENT',
				),
			),
		);
	}

	/**
	 * @dataProvider parseAddressProvider
	 */
	public function testParseAddress($input, $output)
	{
		$input += array(
			'ADDR_TYPE' => '',
			'PROPERTY_NAME' => '',
			'PROPERTY_NO' => '',
			'ADDR1' => '',
			'ADDR2' => '',
			'ADDR3' => '',
			'ADDR4' => '',
			'ADDR5' => '',
			'POSTCODE' => '',
			'DATE_START' => '',
			'DATE_END' => null,
		);

		$output += array(
			'address1' => '',
			'address2' => '',
			'city' => '',
			'county' => '',
			'postcode' => '',
			'country' => 'United Kingdom',
			'address_type' => null,
			'date_start' => '',
			'date_end' => null,
			'id' => null,
			'contact_id' => null,
			'email' => null,
			'last_modified_user_id' => '1',
			'last_modified_date' => '1900-01-01 00:00:00',
			'created_user_id' => '1',
			'created_date' => '1900-01-01 00:00:00',
		);

		$output["country_id"] = Country::model()->findByAttributes(array('name' => $output["country"]))->id;
		unset($output["country"]);

		$output["address_type_id"] = $output["address_type"] ? AddressType::model()->findByAttributes(array('name' => $output["address_type"]))->id : null;
		unset($output["address_type"]);

		$pas_address = ComponentStubGenerator::generate('PAS_PatientAddress', $input);
		$address = new Address;

		PasTransformer::parseAddress($pas_address, $address);

		$this->assertEquals($output, $address->attributes);
	}
}
