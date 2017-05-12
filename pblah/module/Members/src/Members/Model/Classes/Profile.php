<?php

namespace Members\Model\Classes;


use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\ResultSet\ResultSet;

use Members\Model\Classes\Exceptions\ProfileException;



class Profile
{
    /**
     * @var string
     */
    public static $user;

    /**
     * @var TableGateway
     */
    public static $table_gateway;

    /**
     * @var Sql
     */
    public static $sql;

    /**
     * @var array
     */
    public static $profile_changes = array();


    /**
     * @var array
     */
    public static $profile_settings = array(
        'private'               => false,
        'public'                => true,
        'searchable'            => true,
    );


    /**
     * Gets an instance of Zend\Db\TableGateway\TableGateway class
     * @param TableGateway $gateway
     * @return TableGateway|null
     */
    public static function getTableGateway(TableGateway $gateway)
    {
        self::$table_gateway = $gateway instanceof TableGateway ? $gateway : null;

        return self::$table_gateway;
    }


    /**
     * Gets an instance of Zend\Db\Sql\Sql class
     * @return Sql
     */
    public static function getSQLClass()
    {
        self::$sql = new Sql(self::$table_gateway->getAdapter());

        return self::$sql;
    }


    /**
     * Setter method
     * @param string $user
     * @return \Members\Profile
     */
    public static function setUser($user)
    {
        self::$user = $user;

        return new self();
    }


    /**
     * Getter method
     * @return string
     */
    public static function getUser()
    {
        return self::$user;
    }


    /**
     * Gets the user id
     * @return ResultSet|boolean
     */
    public static function getUserId()
    {
        $select = new Select('members');

        $select->columns(array('*'))
        ->where(array('username' => self::getUser()));


        $query = self::getSQLClass()->getAdapter()->query(
            self::$sql->buildSqlString($select),
            Adapter::QUERY_MODE_EXECUTE
        );

        if (count($query) > 0) {
            foreach ($query as $result) {
                $row = $result;
            }

            return $row;
        }

        return false;
    }


    /**
     * Gets display name for user
     * @return string
     */
    public static function getDisplayName()
    {
        $display_name = self::getProfile()['display_name'];

        return $display_name;
    }


    /**
     * Gets the location of the user
     * @return string
     */
    public static function getLocation()
    {
        $location = self::getProfile()['location'];

        return $location;
    }


    /**
     * Gets the age of the user
     * @return number
     */
    public static function getAge()
    {
        $age = self::getProfile()['age'];

        return $age;
    }


    /**
     * Gets the user's bio
     * @return string
     */
    public static function getBio()
    {
        $bio = self::getProfile()['bio'];

        return $bio;
    }



    /**
     * Method to handle editing of profile by member
     * @param array $changes
     * @throws ProfileException
     * @return boolean
     */
    public static function editProfile(array $changes)
    {
        if (count($changes, 1) > 0) {
            foreach ($changes as $key => $value) {
                self::$profile_changes[$key] = $value;
            }


            // proceed to update the profile information
            // that resides in the profiles table
            // locate the id in the profile table
            $select = new Select('profiles');

            $select->columns(array('*'))
            ->where(array('profile_id' => self::getUserId()['id']
            ));

            $query = self::$sql->getAdapter()->query(
                self::$sql->buildSqlString($select),
                Adapter::QUERY_MODE_EXECUTE);

            if (count($query) > 0) {

                foreach ($query as $row) {
                    $data_holder = $row;
                }
                // profile found
                // update the changes now
                $update = new Update('profiles');

                // if the changes are not empty use them
                // if they are, default to the original
                $updated_data = array(
                    'display_name'  => array_key_exists('display_name', self::$profile_changes)
                    ? rtrim(self::$profile_changes['display_name'])  : $data_holder['display_name'],
                    'email_address' => array_key_exists('email_address', self::$profile_changes)
                    ? rtrim(self::$profile_changes['email_address']) : $data_holder['email_address'],
                    'age'           => array_key_exists('age', self::$profile_changes)
                    ? rtrim(self::$profile_changes['age'])           : $data_holder['age'],
                    'location'      => array_key_exists('location', self::$profile_changes)
                    ? rtrim(self::$profile_changes['location'])      : $data_holder['location'],
                    'bio'           => array_key_exists('bio', self::$profile_changes)
                    ? rtrim(self::$profile_changes['bio'])           : $data_holder['bio'],
                );

                $update->set(array(
                    'display_name'  => $updated_data['display_name'],
                    'email_address' => $updated_data['email_address'],
                    'age'           => $updated_data['age'],
                    'location'      => $updated_data['location'],
                    'bio'           => $updated_data['bio'],
                ))->where(array('profile_id' => $data_holder['profile_id']));


                $query = self::$sql->getAdapter()->query(
                    self::$sql->buildSqlString($update),
                    Adapter::QUERY_MODE_EXECUTE
                );

                if (!$query) {
                    throw new ProfileException("Error updating your profile, please try again.");
                }

                return true;
            } else {
                throw new ProfileException("User not found.");
            }
        } else {
            throw new ProfileException("Profile changes cannot be left empty if you wish to edit your profile.");
        }
    }


    /**
     * Removes profile for specified user
     * @throws ProfileException
     * @return boolean
     */
    public static function removeProfile()
    {
        if (!empty(self::getUser())) {
            // delete the profile for the user
            $delete = new Delete('profiles');

            $delete->where(array('profile_id' => self::getUserId()['id']));

            $query = self::$sql->getAdapter()->query(
                self::$sql->buildSqlString($delete),
                Adapter::QUERY_MODE_EXECUTE
            );

            if (!$query) {
                throw new ProfileException("Error removing profile, please try again.");
            }

            return true;
        } else {
            throw new ProfileException("User not found.");
        }
    }


    public static function profileSettings(array $settings)
    {
        if (count($settings) > 0) {
            // compare the setting passed
            // to the list of available settings
            if (in_array($settings, array_keys(self::$profile_settings))) {
                // determine which value(s) were supplied
                $holder = array();

                foreach ($settings as $key => $value) {
                    $holder[$key] = $value;
                }

                switch ($holder) {
                    case $holder['private'] !== false:
                        // profile was set to be private
                        // make it private and only allow friends
                        // to view it
                        self::makeProfilePrivate();
                    break;

                    case $holder['public'] !== false:
                        // profile was set to be public
                        // make it public and viewable by all
                        self::makeProfilePublic();
                    break;
                }
            } else {
                throw new ProfileException("Setting supplied is not valid.");
            }
        }

        return false;
    }


    public static function profileViews()
    {

    }


    /**
     * Creates the profile for the user
     * @param array $data
     * @throws ProfileException
     * @return boolean
     */
    public static function createProfile(array $data)
    {
        if (count($data, 1) > 0) {
            // assign the data to a holder array
            // then insert the data into the profiles table
            $profile_data = array();

            foreach ($data as $key => $value) {
                $profile_data[$key] = $value;
            }

            $insert_data = array(
                'profile_id'    => self::getUserId()['id'],
                'display_name'  => $profile_data['display_name'],
                'email_address' => $profile_data['email_address'],
                'age'           => $profile_data['age'],
                'location'      => $profile_data['location'],
                'bio'           => $profile_data['bio'],
            );

            // insert into profiles table
            $insert = self::$table_gateway->insert($insert_data);

            if ($insert > 0) {
                $update = new Update('members');

                $update->set(array('new' => 0))
                ->where(array('id' => self::getUserId()['id']));

                $query = self::$sql->getAdapter()->query(
                    self::$sql->buildSqlString($update),
                    Adapter::QUERY_MODE_EXECUTE
                );

                if (!$query) {
                    throw new ProfileException("Error creating profile, please try again.");
                }

                // make the profile dir
                mkdir(getcwd() . '/public/images/profile/' . self::getUser());
                mkdir(getcwd() . '/public/images/profile/' . self::getUser() . '/current');

                // make the htaccess file
                file_put_contents(getcwd() . '/public/images/profile/' . self::getUser() . '/.htaccess', 'Options -Indexes');

                return true;
            } else {
                throw new ProfileException("Error inserting data, please try again.");
            }
        } else {
            throw new ProfileException("Profile data cannot be left empty.");
        }
    }


    /**
     * Gets a user profile
     * @throws \ProfileException
     * @return array|boolean
     */
    public static function getProfile()
    {
        $select = new Select('profiles');

        $select->columns(array('*'))
        ->where(array('profile_id' => self::getUserId()['id']));

        $query = self::$sql->getAdapter()->query(
            self::$sql->buildSqlString($select),
            Adapter::QUERY_MODE_EXECUTE
        );



        if (count($query) > 0) {
            foreach ($query as $result) {
                $row = $result;
            }

            return $row;
        } else {
            throw new ProfileException("It looks like you haven't set up a profile yet.");
        }

        return false;
    }






    ////////////////////////////////////////////////////
    // private methods                                //
    ////////////////////////////////////////////////////


    /**
     * Makes the user's profile private
     * @throws ProfileException
     * @return boolean
     */
    private static function makeProfilePrivate()
    {
        // first check if the profile is already set to private
        // if so, abort
        // if it isn't make it private
        $select = new Select('profile_settings');

        $select->columns(array('*'))
        ->where(array('profile_id' => self::getUserId()['id'], 'setting' => 'profile_visibility', 'value' => 1));

        $query = self::$sql->getAdapter()->query(
            self::$sql->buildSqlString($select),
            Adapter::QUERY_MODE_EXECUTE
        );

        if (count($query) > 0) {
            // setting found
            // abort
            throw new ProfileException("Your profile is already set to private.");
        } else {
            // insert the private setting now
            $insert = new Insert('profile_settings');

            $insert->columns(array(
                'profile_id',
                'setting',
                'value'
            ))->values(array(
                'profile_id' => self::getUserId()['id'],
                'setting'    => 'profile_visibility',
                'value'      => 1
            ));

            $query = self::$sql->getAdapter()->query(
                self::$sql->buildSqlString($insert),
                Adapter::QUERY_MODE_EXECUTE
            );

            if (!$query) {
                throw new ProfileException("Error making profile private, please try again.");
            }

            return true;
        }
    }


    /**
     * Makes a user's profile public
     * @throws ProfileException
     * @return boolean
     */
    private static function makeProfilePublic()
    {
        // first check if the profile is already set to public
        // if so, abort
        // if it isn't make it public
        $select = new Select('profile_settings');

        $select->columns(array('*'))
        ->where(array('profile_id' => self::getUserId()['id'], 'setting' => 'profile_visibility', 'value' => 0));

        $query = self::$sql->getAdapter()->query(
            self::$sql->buildSqlString($select),
            Adapter::QUERY_MODE_EXECUTE
        );

        if (count($query) > 0) {
            // setting found
            // abort
            throw new ProfileException("Your profile is already set to public.");
        } else {
            // insert the public setting now
            $insert = new Insert('profile_settings');

            $insert->columns(array(
                'profile_id',
                'setting',
                'value'
            ))->values(array(
                'profile_id' => self::getUserId()['id'],
                'setting'    => 'profile_visibility',
                'value'      => 0,
            ));

            $query = self::$sql->getAdapter()->query(
                self::$sql->buildSqlString($insert),
                Adapter::QUERY_MODE_EXECUTE
            );

            if (!$query) {
                throw new ProfileException("Error making profile public, please try again.");
            }

            return true;
        }
    }
}