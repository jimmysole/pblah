<?php
namespace Application\Model;


use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Insert;
use Zend\Db\Adapter\Adapter;


class VerifyModel
{
    /**
     * @var TableGateway
     */
    protected $table_gateway;


    /**
     * @var mixed
     */
    protected $code;


    /**
     * Constructor method for VerifyModel class
     * @param TableGateway $gateway
     */
    public function __construct(TableGateway $gateway)
    {
        // check if $gateway was passed an instance of TableGateway
        // if so, assign $this->table_gateway the value of $gateway
        // if not, make it null
        $gateway instanceof TableGateway ? $this->table_gateway = $gateway : $this->table_gateway = null;
    }


    public function authenticateCode($code)
    {

        // authenticate the verification code in the url against the one in the pending_users table
        $this->code = !empty($code) ? $code : null;

        $select = $this->table_gateway->select(array('pending_code' => $this->code));

        $row = $select->current();

        if (!$row) {
            throw new \RuntimeException(sprintf('Invalid registration code %s', $this->code));
        } else {
            // verification code was found
            // proceed to remove the user from the pending_users table
            // and insert into the members table
            $data = array(
                'username' => $row['username'],
                'password' => $row['password'],
            );

            $sql = new Sql($this->table_gateway->getAdapter());

            $adapter = $this->table_gateway->getAdapter();

            $insert = new Insert('members');

            $insert->columns(array(
                'username',
                'password',
                'new',
            ))->values(array(
                'username' => $data['username'],
                'password' => $data['password'],
                'new'      => 1,
            ));

            $execute = $adapter->query(
                $sql->buildSqlString($insert),
                Adapter::QUERY_MODE_EXECUTE
            );


            if (count($execute) > 0) {
                // remove the entry now
                $delete = $this->table_gateway->delete(array('pending_code' => $this->code));

                if ($delete > 0) {
                    return true;
                }
            }
        }
    }
}