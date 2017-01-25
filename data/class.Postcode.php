<?php

/*

class.Postcode.php

A class containing functions for handling postcodes

Created by Stephen Morley - http://stephenmorley.org/ - and released under the
terms of the CC0 1.0 Universal legal code:

http://creativecommons.org/publicdomain/zero/1.0/legalcode

*/

/* A class containing functions for handling postcodes, including validation of
 * postcode formats and extraction of parts of postcodes.
 */
class Postcode{

  // define a regular expression that matches the general format for postcodes
  const REGULAR_EXPRESSION =
      '/^\s*(([A-Z]{1,2})[0-9][0-9A-Z]?)\s*(([0-9])[A-Z]{2})\s*$/';

  // define the field lengths in the database
  const DISTRICT_LENGTH  = 4;
  const POST_TOWN_LENGTH = 22;
  const RECORD_LENGTH    = 26;

  // declare the path to the database
  private static $databasePath;

  /* Returns whether a postcode is in a valid format. The parameter is:
   *
   * $postcode - the postcode whose format should be checked
   */
  public static function isValidFormat($postcode){

    // return whether the postcode is in a valid format
    return preg_match(self::REGULAR_EXPRESSION, strtoupper($postcode));

  }

  /* Parses a postcode and returns an array with the following components:
   *
   * 1 - the outward code
   * 2 - the area from the outward code
   * 3 - the inward code
   * 4 - the sector from the inward code
   *
   * The parameter is:
   *
   * $postcode - the postcode to parse
   */
  private static function parse($postcode){

    // parse the postcode and return the result
    preg_match(self::REGULAR_EXPRESSION, strtoupper($postcode), $matches);
    return $matches;

  }

  /* Returns the area for a postcode - for example, SW for SW1A 0AA - or false
   * if the postcode was not in a valid format. The parameter is:
   *
   * $postcode - the postcode whose area should be returned
   */
  public static function getArea($postcode){

    // parse the postcode and return the area
    $parts = self::parse($postcode);
    return (count($parts) > 0 ? $parts[2] : false);

  }

  /* Returns the district for a postcode - for example, SW1A for SW1A 0AA - or
   * false if the postcode was not in a valid format. The parameter is:
   *
   * $postcode - the postcode whose district should be returned
   */
  public static function getDistrict($postcode){

    // parse the postcode and return the district
    $parts = self::parse($postcode);
    return (count($parts) > 0 ? $parts[1] : false);

  }

  /* Returns the sector for a postcode - for example, SW1A 0 for SW1A 0AA - or
   * false if the postcode was not in a valid format. The parameter is:
   *
   * $postcode - the postcode whose sector should be returned
   */
  public static function getSector($postcode){

    // parse the postcode and return the sector
    $parts = self::parse($postcode);
    return (count($parts) > 0 ? $parts[1] . ' ' . $parts[4] : false);

  }

  /* Returns the unit for a postcode - for example, SW1A 0AA for SW1A 0AA - or
   * false if the postcode was not in a valid format. The parameter is:
   *
   * $postcode - the postcode whose unit should be returned
   */
  public static function getUnit($postcode){

    // parse the postcode and return the unit
    $parts = self::parse($postcode);
    return (count($parts) > 0 ? $parts[1] . ' ' . $parts[3] : false);

  }

  /* Returns the post town for a postcode, or the empty string if the post town
   * could not be determined. The parameter is:
   *
   * $postcode - the postcode whose post town should be returned
   */
  public static function getPostTown($postcode){

    // determine the district for the postcode
    $district = self::getDistrict($postcode);

    // return immediately if the postcode was not in a valid format
    if (!$district) return false;

    // check whether the path to the database has been initialised
    if (!self::$databasePath){

      // initialise the path to the database
      self::$databasePath =
          dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Postcode.data';

      // throw an error if the database is missing
      if (!is_file(self::$databasePath)){
        trigger_error(
            'Postcode.data not found - ensure you have downloaded the database as well as the PHP class',
            E_USER_ERROR);
      }

    }

    // open the database
    $database = fopen(self::$databasePath, 'r');

    // initialise the search bounds
    $start = 0;
    $end   = filesize(self::$databasePath) / self::RECORD_LENGTH - 1;

    // loop until the bounds have crossed over
    do{

      // determine the index of the midpoint
      $midpoint = ($start + $end) >> 1;

      // determine the district for the midpoint
      fseek($database, $midpoint * self::RECORD_LENGTH);
      $midpointDistrict = rtrim(fread($database, self::DISTRICT_LENGTH));

      // update the bounds or store the post town depending on the district
      if ($district < $midpointDistrict){
        $end = $midpoint - 1;
      }elseif ($district > $midpointDistrict){
        $start = $midpoint + 1;
      }else{
        $postTown = rtrim(fread($database, self::POST_TOWN_LENGTH));
        break;
      }

    }while ($start <= $end);

    // close the database
    fclose($database);

    // return the post town
    return (isset($postTown) ? $postTown : '');

  }

}

?>
