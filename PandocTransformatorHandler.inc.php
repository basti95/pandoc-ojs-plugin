<?php

import('lib.pkp.classes.plugins.PluginRegistry');

class TransformatorHandler extends Handler  {
    var $_isBackendPage = true;
    /* @var $request PKPRequest*/
    public function parse($args, $request): JSONMessage
    {
        /* @var $plugin Plugin*/
        $plugin = PluginRegistry::getAllPlugins()["pandoctransformatorslubplugin"];
        $pluginPath = $plugin->getPluginPath();
        $pandoc_executable = $pluginPath.'\pandoc.exe';
        $pandoc_executable = str_replace('/','\\',$pandoc_executable);
        $filesDir = Config::getVar('files', 'files_dir');
        $source_file = $filesDir.'\\'.$request->getUserVar('filePath');

        $source_file = str_replace('/','\\', $source_file);
        $target_file = str_replace('.docx','.html',$source_file);
        $tmp_file_path = "test.html";
        error_log(exec($pandoc_executable.' -o '.$tmp_file_path.' '.$source_file));
        #Datei liegt nun mit anderer Endung transformiert vor.
        /* @var $fileService \PKP\Services\PKPFileService */
        $fileService = Services::get('file');
        $newFileId = $fileService->add($tmp_file_path,$target_file);

        /* @var $submissionFileDao PKPSubmissionFileDAO */
        $submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
        $newFileInDB = $submissionFileDao->newDataObject();
        $newFileInDB->setAllData([
                'fileId' => $newFileId,
				'assocType' => $request->getUserVar('assocType'),
				'assocId' => $request->getUserVar('assocId'),
				'fileStage' => $request->getUserVar('fileStage'),
				'mimetype' => 'application/html',
				'locale' => $request->getUserVar('locale'),
				'genreId' => $request->getUserVar('genreId'),
				'name' => 'sample.html',
				'submissionId' => $request->getUserVar('submissionId'),
        ]);
        $newSubmissionFile = Services::get('submissionFile')->add($newFileInDB, $request);
        # TODO: Verschieben der Datei funktioniert noch nicht, erstellung mit dem Pandoc-Befehl klappt aber nächster Schritt ist bereinigen, bestenfalls wird nur ID der alten submission File genutzt aus der Anfrage um die neue Datei zu erzeugen. Außerdem sollte neue UniqueId für die abgelegte datei verwendet werden.
        # BSP: https://github.com/Vitaliy-1/docxConverter/blob/main/DocxToJatsPlugin.inc.php
        return new JSONMessage(true);
    }
}