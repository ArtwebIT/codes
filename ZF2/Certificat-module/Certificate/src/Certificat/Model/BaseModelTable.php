<?php

/**
 * Base Model Table for Certificat
 */

namespace Certificat\Model;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Table class
 */
abstract class BaseModelTable implements ServiceLocatorAwareInterface
{

    /**
     * @var TableGateway
     */
    protected $tableGateway;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var ViewRenderer
     */
    protected $renderer;

    /**
     * @var \Zend\Mvc\I18n\Translator
     */
    protected $translator;

    /**
     *
     * @var \SNJ\Model\UserTable
     */
    protected $userTable;

    /**
     *
     * @var \Certificat\Model\UserOrganizationTable
     */
    protected $userOrganizationTable;

    /**
     *
     * @var \Certificat\Model\OrganizationTable
     */
    protected $organizationTable;

    /**
     * Set service locator.
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get service locator to access service from module configuration.
     *
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * Get translator from service manager.
     *
     * @return    \Zend\Mvc\I18n\Translator
     */
    public function getTranslator()
    {
        if (!$this->translator) {
            $this->translator = $this->getServiceLocator()->get('translator');
        }

        return $this->translator;
    }

    /**
     * Get ViewRenderer from service manager.
     *
     * @return    \Zend\View\Renderer\PhpRenderer
     */
    public function getViewRenderer()
    {
        if (!$this->renderer) {
            $this->renderer = $this->getServiceLocator()->get('ViewRenderer');
        }

        return $this->renderer;
    }

    /**
     * Get user table via service manager
     *
     * @return \SNJ\Model\UserTable
     */
    public function getUserTable()
    {
        if (!$this->userTable) {
            $this->userTable = $this->getServiceLocator()->get('UserTable');
        }
        return $this->userTable;
    }

    /**
     * Get user organization table via service manager
     *
     * @return \Certificat\Model\UserOrganizationTable
     */
    public function getUserOrganizationTable()
    {
        if (!$this->userOrganizationTable) {
            $this->userOrganizationTable = $this->getServiceLocator()->get('Certificat\Model\UserOrganizationTable');
        }
        return $this->userOrganizationTable;
    }

    /**
     * Get organization table via service manager
     *
     * @return \Certificat\Model\OrganizationTable
     */
    public function getOrganizationTable()
    {
        if (!$this->organizationTable) {
            $this->organizationTable = $this->getServiceLocator()->get('Certificat\Model\OrganizationTable');
        }
        return $this->organizationTable;
    }

    /**
     * Constructor method for table class.
     *
     * @param \Zend\Db\TableGateway\TableGateway $tableGateway
     */
    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    /**
     * Send Email
     *
     * @param   string  $to
     * @param   string  $subject
     * @param   string  $content
     * @param   string  $fromEmail
     * @param   string  $fromName
     * @return  \Zend\View\Renderer\PhpRenderer
     */
    public function sendEmail($to, $subject, $content, $fromEmail = null, $fromName = null)
    {
        $config = $this->getServiceLocator()->get('config');
        $mailConfig = $config['mail'];

        if (is_null($fromEmail)) {
            $fromEmail = $mailConfig['sender']['no-reply']['email'];
        }

        if (is_null($fromName)) {
            $fromName = $mailConfig['sender']['no-reply']['name'];
        }

        $html = new \Zend\Mime\Part($content);
        $html->type = \Zend\Mime\Mime::TYPE_HTML;
        $html->charset = 'utf-8';
        $body = new \Zend\Mime\Message();
        $body->setParts(array($html,));

        $mail = new \Zend\Mail\Message();
        $mail->setBody($body);
        $mail->setFrom($fromEmail, $fromName);
        $mail->setTo($to);
        $mail->setSubject($subject);

        $headers = $mail->getHeaders();
        $headers->removeHeader('Content-Type');
        $headers->addHeaderLine('Content-Type', 'text/html; charset=UTF-8');

        $options = new \Zend\Mail\Transport\SmtpOptions($mailConfig['smtp']);

        $transport = new \Zend\Mail\Transport\Smtp($options);
        $transport->send($mail);
    }
    
    /**
     * Insert multiple rows in table by single query.
     *
     * @param array $data Rows for insert
     */        
    protected function insertBatch(array $data)
    {
        $adapter = $this->tableGateway->getAdapter();
        $platform = $adapter->getPlatform();

        // Generate column names from array keys
        $columns = (array) current($data);
        $columns = array_keys($columns);
        $columnsCount = count($columns);
        array_filter($columns, function (&$item) use ($platform) {
            $item = $platform->quoteIdentifier($item);
        });
        $columns = "(" . implode(',', $columns) . ")";

        // Generate the placeholder for insert
        $placeholder = array_fill(0, $columnsCount, '?');
        $placeholder = "(" . implode(',', $placeholder) . ")";
        $placeholder = implode(',', array_fill(0, count($data), $placeholder));

        // Generate values for insert
        $values = array();
        foreach ($data as $row) {
            foreach ($row as $key => $value) {
                $values[] = $value;
            }
        }

        $table = $platform->quoteIdentifier($this->tableGateway->getTable());
        $sql = "INSERT INTO $table $columns VALUES $placeholder";
        $adapter->query($sql)->execute($values);
    }

    /**
     * Update multiple rows in table by single query.
     *
     * @param array $data Rows for update
     * @param int $index Field for update by
     * @param array $where Additional condition for query
     */    
    protected function updateBatch(array $data, $index, array $where = array())
    {
        if (!empty($data)) {
            $adapter = $this->tableGateway->getAdapter();
            $platform = $adapter->getPlatform();
            $table = $platform->quoteIdentifier($this->tableGateway->getTable());

            $ids = array();
            $final = array();
            $where = ($where != '' AND count($where) >= 1) ? implode(" ", $where) . ' AND ' : '';

            foreach ($data as $val) {
                $ids[] = $val[$index];

                foreach (array_keys($val) as $field) {
                    if ($field != $index) {
                        $curValue = (is_null($val[$field]) ? "NULL" : $platform->quoteValue($val[$field]));
                        $curIndexValue = $platform->quoteValue($val[$index]);
                        $final[$field][] = 'WHEN ' . $platform->quoteIdentifier($index) . ' = ' . $curIndexValue . ' THEN ' . $curValue;
                    }
                }
            }

            $sql = "UPDATE " . $table . " SET ";
            $cases = '';

            foreach ($final as $k => $v) {
                $k = $platform->quoteIdentifier($k);
                $cases .= $k . ' = CASE ' . "\n";
                foreach ($v as $row) {
                    $cases .= $row . "\n";
                }

                $cases .= 'ELSE ' . $k . ' END, ';
            }

            $sql .= substr($cases, 0, -2);
            $sql .= ' WHERE ' . $where . $index . ' IN ("' . implode('","', $ids) . '")';
            $adapter->query($sql)->execute();
        }
    }    

}
