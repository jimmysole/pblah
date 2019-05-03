<?php
namespace Application\Model;


use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Insert;
use Zend\Db\Adapter\Adapter;

use Zend\View;
use Zend\View\Model\ViewModel;

use Zend\Mime\Message;
use Zend\Mime\Part;

use Zend\Mail\Message as MailMessage;
use Zend\Mail\Transport\Sendmail;

use Application\Model\Filters\Register;



class RegisterModel
{
    /**
     * @var TableGateway|null
     */
    protected $table_gateway;


    /**
     *
     * @var Sql
     */
    protected $sql;


    /**
     * @var AdapterInterface
     */
    protected $adapter;


    /**
     * @var mixed
     */
    public $username;


    /**
     * @var mixed
     */
    public $password;


    /**
     * @var mixed
     */
    public $email;


    /**
     * @var mixed
     */
    public $pending_code;



    /**
     * Constructor method for RegisterModel class
     * @param TableGateway $gateway
     */
    public function __construct(TableGateway $gateway)
    {
        // check if $gateway was passed an instance of TableGateway
        // if so, assign $this->table_gateway the value of $gateway
        // if not, make it null
        $gateway instanceof TableGateway ? $this->table_gateway = $gateway : $this->table_gateway = null;

        $this->sql = new Sql($this->table_gateway->getAdapter());

        $this->adapter = $this->table_gateway->getAdapter();
    }


    /**
     * Handles the registration part
     * @param Register $register
     * @return string|boolean
     * @throws \Exception
     */
    public function handleRegistration(Register $register)
    {
        // get the registration data
        // and assign it to the objects $username, $password and $email
        $this->username = !empty($register->username)      ? $register->username                                  : null;
        $this->password = !empty($register->password)      ? password_hash($register->password, PASSWORD_DEFAULT) : null;
        $this->email    = !empty($register->email_address) ? $register->email_address                             : null;

        // see if there is a registration that is pending already
        // if so, stop and show message that says registration is pending
        // if not, continue with the registration process
        $select = new Select('pending_users');

        $select->columns(array('username'))
        ->where(array('username' => $this->username));

        $query = $this->adapter->query(
            $this->sql->buildSqlString($select),
            Adapter::QUERY_MODE_EXECUTE
        );

        if (count($query) > 0) {
            // username is already pending
            // return false
            return false;
        } else {
            $this->pending_code = md5($this->username . $this->email);

            // insert the info into pending users table
            $insert = new Insert('pending_users');

            $insert->columns(array(
                'username', 'password', 'email', 'pending_code'
            ))->values(array('username' => $this->username, 'password' => $this->password,
                'email' => $this->email, 'pending_code' => $this->pending_code
            ));

            $query = $this->adapter->query(
                $this->sql->buildSqlString($insert),
                Adapter::QUERY_MODE_EXECUTE
            );

            if (count($query) > 0) {
                // send the verification email to the email address provided
                // set the template to be used for pretty looking emails
                $view = new View\Renderer\PhpRenderer();
                $resolver = new View\Resolver\TemplateMapResolver();

                $resolver->setMap(array(
                    'mailTemplate' => __DIR__ . '/../../../view/email/email.phtml',
                ));

                $view->setResolver($resolver);

                $view_model = new ViewModel();

                $view_model->setTemplate('mailTemplate')->setVariables(array(
                    'name'          => $this->username,
                    'link'          => $_SERVER['SERVER_NAME'] . '/verify/' . $this->pending_code,
                ));

                // set the mime to text/html
                $body_part = new Message();

                $body_message = new Part($view->render($view_model));

                $body_message->type = 'text/html';

                $body_part->setParts(array($body_message));


                // actual building and sending of message
                $message = new MailMessage();

                $message->addFrom('register@pblah.com')
                    ->addTo($this->email)
                    ->setSubject('Verification Process for registering for p-Blah forums')
                    ->setBody($body_part)
                    ->setEncoding('UTF-8');

                $transport = new Sendmail();

                $transport->send($message);

                return true;
            } else {
                throw new \Exception("Error inserting.");
            }
        }

        return false;
    }
}