<?php

/**
 * Table class for certificate participant.
 */

namespace Certificat\Model;

/**
 * Table class
 */
class CertificateParticipantTable extends BaseModelTable
{

    /**
     * Sync participant for given certificate
     *
     * @param int $certificate_id
     * @param array $participants
     */
    public function syncParticipantsByCertificateId($certificate_id, array $participants)
    {
        $oldEmails = $this->getParticipantsEmailsByCertificateId($certificate_id);

        $newEmails = array();
        foreach ($participants as $participant) {
            $newEmails[] = $participant['email'];
        }

        // Get and delete removed participants
        $emailsToDelete = array_diff($oldEmails, $newEmails);
        $this->deleteParticipantsFromCertificateByEmails($certificate_id, $emailsToDelete);


        $emailsToInsert = array_diff($newEmails, $oldEmails);
        $participantsToInsert = array();

        foreach ($participants as &$participant) {
            if ($participant['birthday'] != '') {
                // strtotime not working with date in format dd/mm/yyyy
                $participant['birthday'] = str_replace('/', '-', $participant['birthday']);
                $participant['birthday'] = date('Y-m-d', strtotime($participant['birthday']));
            } else {
                $participant['birthday'] = NULL;
            }

            if ($participant['comment'] == '') {
                $participant['comment'] = NULL;
            }

            // Get new added participants
            if (in_array($participant['email'], $emailsToInsert)) {
                $participantsToInsert[] = $participant;
                unset($participant);
            }
        }
        unset($participant);

        // Insert new added participants
        $this->multiInsertParticipants($participantsToInsert, $certificate_id);

        // Update existing participants
        $this->multiUpdateParticipants($participants, 'email', $certificate_id);
    }

    /**
     * Add participants to given certificate
     *
     * @param array $data
     * @param int $certificate_id
     */
    public function multiInsertParticipants(array $data, $certificate_id)
    {
        if (count($data) > 0) {
            foreach ($data as &$row) {
                $row['certificate_id'] = (int) $certificate_id;
                $row['created'] = date('Y-m-d H:i:s');
            }
            unset($row);

            try {
                return $this->insertBatch($data);
            } catch (\Exception $ex) {
                throw new \Exception($this->getTranslator()->translate("Unable to add participants to certificate #$certificate_id"));
            }
        }
    }

    /**
     * Update participants 
     *
     * @param array $data
     * @param string $index Field for update by
     * @param int $certificate_id
     */
    public function multiUpdateParticipants(array $data, $index, $certificate_id)
    {
        if (count($data) > 0) {
            $where = array('certificate_id = ', (int) $certificate_id);
            try {
                return $this->updateBatch($data, $index, $where);
            } catch (\Exception $ex) {
                throw new \Exception($this->getTranslator()->translate("Unable to update participants from certificate #$certificate_id"));
            }
        }
    }

    /**
     * Delete participants from given certificate by emails
     *
     * @param int $certificate_id
     * @param array $emailsToDelete
     */
    public function deleteParticipantsFromCertificateByEmails($certificate_id, array $emailsToDelete = array())
    {
        if (!empty($emailsToDelete)) {
            try {
                $delete = $this->tableGateway->getSql()->delete();
                $delete->where('certificate_id', (int) $certificate_id)
                ->where->in('email', $emailsToDelete);
                $this->tableGateway->deleteWith($delete);
            } catch (\Exception $ex) {
                throw new \Exception($this->getTranslator()->translate("Unable to remove participants from certificate"));
            }
        }
    }

    /**
     * Get participant`s emails by certificate_id
     * 
     * @param int $certificate_id 
     * @return array
     */
    public function getParticipantsEmailsByCertificateId($certificate_id)
    {
        $result = array();
        $items = $this->tableGateway->select(array('certificate_id' => (int) $certificate_id))
                ->toArray();
        if (count($items) > 0) {
            foreach ($items as $item) {
                $result[] = $item['email'];
            }
        }

        return $result;
    }

    /**
     * Get all entries in table by certificate_id
     * 
     * @param int $certificate_id 
     * @return array|ArrayObject
     */
    public function getParticipantsByCertificateId($certificate_id)
    {
        $select = $this->tableGateway->getSql()
                ->select()
                ->where(array('certificate_id' => (int) $certificate_id))
                ->order('id ASC');

        return $this->tableGateway->selectWith($select);
    }

    /**
     * Get entry by id in table via table gateway
     * 
     * @param   int     $id
     * @return  array|\ArrayObject|null
     * @throws  \Exception
     */
    public function getParticipant($id)
    {
        $rowset = $this->tableGateway->select(array('id' => (int) $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception($this->getTranslator()->translate("Could not find certificate participant #$id"));
        }
        return $row;
    }
    
    /**
     * Delete record by given id
     *
     * @param int $id
     */
    public function deleteParticipant($id)
    {
        $this->tableGateway->delete(array('id' => (int) $id));
    }

}
