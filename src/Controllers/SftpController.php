<?php

namespace Mi2\SFTP\Controllers;

use Mi2\CrisisPrep\CrisisPrepEventHandler;
use Mi2\Framework\AbstractController;
use Mi2\SFTP\Services\SFTPService;
use OpenEMR\Common\Csrf\CsrfUtils;

class SftpController extends AbstractController
{
    public function __construct()
    {

    }

    /**
     * Starting point for mss (link from admin menu)
     * Display the view with upload form.
     *
     * Also display a list of previous batches, and when one is selected,
     * display a report of the changes made in that batch.
     */
    public function _action_import()
    {
        // Get the column mapping
        $this->view->columns = SFTPService::getColumns();

        // Specify which view script to display
        $this->view->action_url = $this->getBaseUrl() . '/index.php?action=sftp!do_import';
        $this->view->ajax_source_url = $this->getBaseUrl() . '/index.php?action=sftp!ajax_source';
        $this->setViewScript('sftp/sftp.php');
    }

    public function _action_ajax_source()
    {
        // not calling from cron job so ensure passes csrf check
        if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
            CsrfUtils::csrfNotVerified();
        }

        // Get the files in the last week
        $batches_result = SFTPService::fetchFilesSince();
        $response = new \stdClass();
        $response->data = [];
        while ($batch = sqlFetchArray($batches_result)) {
            $element = new \stdClass();
            $element->batch_id = $batch['batch_id'];
            $element->file_id = $batch['file_id'] ?: '--';
            $element->server_id = $batch['server_id'];
            $element->batch_type = $batch['batch_type'];
            if ($batch['file_status'] != null) {
                $element->status = $batch['file_status'];
            } else {
                $element->status = $batch['batch_status'];
            }
            $element->filename = $batch['filename'] ?: '--';

            if ($batch['file_created_date'] != null) {
                $element->date_created = $batch['file_created_date'];
            } else {
                $element->date_created = $batch['batch_created_date'];
            }
            // Get array of batch messages
            $batch_messages = SFTPService::fetchMessagesForBatchId($batch['batch_id']);

            // Also Get file messages
            $file_messages = SFTPService::fetchMessagesForFileId($batch['file_id']);

            $element->messages = $batch_messages + $file_messages;
            $response->data[] = $element;
        }

        echo json_encode($response);
        exit();
    }

    public function _action_ajax_refresh_fetch()
    {
        // First let's always check for new files
        $server = \Mi2\SFTP\Services\SFTPService::makeServerUsingGlobalsId(CrisisPrepEventHandler::NS_SERVER_ID);
        if ($server === null) {
            echo "Server `{$server->getId()}` not found\n";
            exit;
        }

        if ($server->isFetchEnabled()) {
            $batch = new \Mi2\SFTP\Models\FetchFileBatch($server);
            $batch->fetch();
        }
        exit();
    }

    /**
     * Get the uploaded spreadsheet
     *
     * Do some basic validation on the file so it won't break
     * the backround service.
     *
     * Store the file on the server and create a row in the DB
     * for this batch with a reference to the file and an indicator
     * that it has not been processed.
     */
    public function _action_do_import()
    {
        if (isset($_POST['do_import'])) {
            // Insert the background service entry in case it doesn't exist.
            ImportManager::insertBackgroundService();

            $files = ImportManager::reArrayFiles($_FILES['input_files']);

            foreach ($files as $file) {
                // Set the file we're using to create the batch
                // $file contains an assos array with all the file's data
                $this->importManager->setUploadFile($file);

                // Do basic validation on file
                // Store messages and display them on the UI
                $valid = $this->importManager->validateFile();

                // If the file is valid, store the tmp file in a real document directory.
                // Create the batch, set to "waiting" so the background process
                // can pick it up and process it.
                if (true === $valid) {
                    $this->importManager->createBatch();
                }
            }
        }

        // Now just do all the things the mss function does for rendering the index page
        $this->_action_import();
    }

    public function _action_do_row_action()
    {
        $what = $this->request->getParam('what');
        $id = $this->request->getParam('id');
        if ($what == 'delete') {
            Batch::delete($id);
        } else if ($what == 'rerun') {
            // Put batch back in waiting status
            Batch::update($id, [
                'status' => Batch::STATUS_WAIT
            ]);
        }
        $response = new Response(200, "Success");
        echo $response->toJson();
        exit;
    }
}
