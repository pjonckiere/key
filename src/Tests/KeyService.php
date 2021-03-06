<?php

/**
 * @file
 * Definition of Drupal\key\Tests\KeyService.
 */

namespace Drupal\key\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests the key service.
 *
 * @group key
 */
class KeyService extends WebTestBase {

  public static $modules = array('key', 'dblog');

  /**
   * Test getKeyValue functions.
   */
  function testConfigKeyProviderService() {

    // Create user with permission to administer keys.
    $user1 = $this->drupalCreateUser(array('administer keys'));
    $this->drupalLogin($user1);

    // Create new key using the Configuration key provider.
    $test_string = 'testing 123 &*#';
    $this->drupalGet('admin/config/security/key/add');
    $edit = [
      'key_provider' => 'config',
    ];
    $this->drupalPostAjaxForm(NULL, $edit, 'key_provider');

    $edit = [
      'id' => 'testing_key',
      'label' => 'Testing Key',
      'description' => 'A test of the Configuration key provider.',
      'key_provider' => 'config',
      'key_settings[key_value]' => $test_string,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));


    // Test getKeyValue service.
    $key_value_string = \Drupal::service('key_repository')->getKey('testing_key')->getKeyValue();

    $this->verbose('Key Value: ' . $key_value_string);

    $this->assertEqual($key_value_string, $test_string, 'The getKeyValue function is not properly processing');

    // Test getKeysByProvider service.
    $keys = \Drupal::service('key_repository')->getKeysByProvider('config');
    $this->assertEqual(count($keys), '1', 'The getKeysByProvider function is not returning 1 config key');

    // Create another key using the Configuration key provider.
    $test_string = 'testing 12345678 (837#';
    $this->drupalGet('admin/config/security/key/add');
    $edit = [
      'key_provider' => 'config',
    ];
    $this->drupalPostAjaxForm(NULL, $edit, 'key_provider');

    $edit = [
      'id' => 'testing_key2',
      'label' => 'Testing Key 2',
      'description' => 'A second test of the Configuration key provider.',
      'key_provider' => 'config',
      'key_settings[key_value]' => $test_string,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));

    // Test getKeysByProvider service.
    $keys = \Drupal::service('key_repository')->getKeysByProvider('config');
    $this->assertEqual(count($keys), '2', 'The getKeysByProvider function is not returning 2 config keys');
  }

  /**
   * Test getKeyValue functions.
   */
  function testFileKeyProviderService() {
    $rpath = realpath(drupal_get_path('module','key').'/tests/assets/testkey.txt');
    $contents = file_get_contents($rpath);

    // Create user with permission to administer keys.
    $user1 = $this->drupalCreateUser(array('administer keys'));
    $this->drupalLogin($user1);

    // Create a new file key.
    $this->drupalGet('admin/config/security/key/add');
    $edit = [
      'key_provider' => 'file',
    ];
    $this->drupalPostAjaxForm(NULL, $edit, 'key_provider');

    $edit = [
      'id' => 'testing_key_file',
      'label' => 'Testing Key File',
      'description' => 'A test of the file key provider.',
      'key_provider' => 'file',
      'key_settings[file_location]' => $rpath,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));

    // Test getKeyValue service.
    $key_value_string = \Drupal::service('key_repository')->getKey('testing_key_file')->getKeyValue();

    $this->verbose('Key Value: ' . $key_value_string);

    $this->assertEqual($key_value_string, $contents, 'The getKeyValue function is not properly processing');

    // Test getKeysByStorageMethod service.
    $keys = \Drupal::service('key_repository')->getKeysByStorageMethod('file');
    $this->assertEqual(count($keys), '1', 'The getKeysByStorageMethod function is not returning 1 file key');

    // Create a second new file key.
    $this->drupalGet('admin/config/security/key/add');
    $edit = [
      'key_provider' => 'file',
    ];
    $this->drupalPostAjaxForm(NULL, $edit, 'key_provider');

    $edit = [
      'id' => 'testing_key_file2',
      'label' => 'Testing Key File2',
      'description' => 'A second test of the file key provider.',
      'key_provider' => 'file',
      'key_settings[file_location]' => $rpath,
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));

    // Test getKeysByStorageMethod service.
    $keys = \Drupal::service('key_repository')->getKeysByStorageMethod('file');
    $this->assertEqual(count($keys), '2', 'The getKeysByStorageMethod function is not returning 2 file keys');

  }

}
