<?php

/**
 * Table class for certificate.
 */

namespace Certificat\Model;

use Zend\Db\Sql\Expression;

/**
 * Table class
 */
class CertificateTable extends BaseModelTable
{

    /**
     * Get all entries in table by organization_id via table gateway
     * 
     * @param int $organization_id 
     * @param bool $archived Archived or Active. By default active 
     */
    public function getByOrganizationId($organization_id, $archived = false)
    {
        $select = $this->tableGateway->getSql()
                ->select()
                ->join(
                        'ce_certificate_participant', 'ce_certificate_participant.certificate_id = ce_certificate.id', array(
                    'participant_count' => new Expression('COUNT(ce_certificate_participant.id)')
                        ), 'left'
                )
                ->join('ce_template', 'ce_template.id = ce_certificate.template_id', array('template_name' => 'name'))
                ->join('user', 'user.id = ce_certificate.user_id', array('user_first_name' => 'first_name', 'user_last_name' => 'last_name'))
                ->where(array('ce_certificate.organization_id' => (int) $organization_id))
                ->group('ce_certificate.id')
                ->order('ce_certificate.name ASC');

        if ($archived) {
            $select->where->equalTo('ce_certificate.status', Certificate::STATUS_ARCHIVED);
        } else {
            $select->where->notEqualTo('ce_certificate.status', Certificate::STATUS_ARCHIVED);
        }

        return $this->tableGateway->selectWith($select);
    }

    /**
     * Get entry by id in table via table gateway
     * 
     * @param   int     $id
     * @return  array|\ArrayObject|null
     * @throws  \Exception
     */
    public function getCertificate($id)
    {
        $select = $this->tableGateway->getSql()
                ->select()
                ->join('ce_template', 'ce_template.id = ce_certificate.template_id', array('template_name' => 'name'))
                ->join('user', 'user.id = ce_certificate.user_id', array('user_first_name' => 'first_name', 'user_last_name' => 'last_name'))
                ->where(array('ce_certificate.id' => (int) $id));

        $rowset = $this->tableGateway->selectWith($select);
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception($this->getTranslator()->translate("Could not find certificate #$id"));
        }
        return $row;
    }

    /**
     * Save certificate as new record or update an existing record.
     * 
     * @param \Certificat\Model\Certificate $certificate
     * @throws \Exception
     */
    public function saveCertificate(Certificate $certificate)
    {
        $data = array(
            'user_id' => $certificate->user_id,
            'organization_id' => $certificate->organization_id,
            'template_id' => $certificate->template_id,
            'name' => $certificate->name,
            'status' => $certificate->status,
            'language' => $certificate->language,
            'description' => $certificate->description,
            'duration' => $certificate->duration,
            // strtotime not working with date in format dd/mm/yyyy
            'start_date' => date('Y-m-d', strtotime(str_replace('/', '-', $certificate->start_date))),
            'end_date' => date('Y-m-d', strtotime(str_replace('/', '-', $certificate->end_date))),
        );

        $id = (int) $certificate->id;

        try {
            if (empty($id)) {
                $data['created'] = date('Y-m-d H:i:s');
                $this->tableGateway->insert($data);
                $id = $this->tableGateway->lastInsertValue;
            } else {
                $this->tableGateway->update($data, array('id' => $id));
            }
        } catch (\Exception $ex) {
            throw new \Exception($this->getTranslator()->translate('Failed to save'));
        }

        return $id;
    }

    /**
     * Change certificate status to `archived`
     * 
     * @param \Certificat\Model\Certificate $certificate
     * @throws \Exception
     */
    public function archiveCertificate(Certificate $certificate)
    {
        return $this->_setCertificateStatus($certificate, Certificate::STATUS_ARCHIVED);
    }

    /**
     * Change certificate status to `completed`
     * 
     * @param \Certificat\Model\Certificate $certificate
     * @throws \Exception
     */
    public function completeCertificate(Certificate $certificate)
    {
        return $this->_setCertificateStatus($certificate, Certificate::STATUS_COMPLETED);
    }

    /**
     * Change certificate status
     * 
     * @param \Certificat\Model\Certificate $certificate
     * @param string $status
     * @throws \Exception
     */
    private function _setCertificateStatus(Certificate $certificate, $status)
    {
        $id = (int) $certificate->id;

        try {
            $this->tableGateway->update(array('status' => $status), array('id' => $id));
        } catch (\Exception $ex) {
            throw new \Exception($this->getTranslator()->translate('Failed to archive certificate'));
        }

        return $id;
    }

    /**
     * Delete record by given id
     *
     * @param int $id
     */
    public function deleteCertificate($id)
    {
        $this->tableGateway->delete(array('id' => (int) $id));
    }

}
