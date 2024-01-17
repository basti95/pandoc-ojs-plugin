<?php

import('lib.pkp.classes.plugins.GenericPlugin');
import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');
class PandocTransformatorSLUBPlugin extends GenericPlugin
{
    public function register($category, $path, $mainContextId = NULL)
    {
        // Register the plugin even when it is not enabled
        $success = parent::register($category, $path);

        if ($success && $this->getEnabled()) {
            HookRegistry::register('TemplateManager::fetch', array($this, 'showConversionButton'));
            HookRegistry::register('LoadHandler', array($this, 'callbackLoadHandler'));
        }

        return $success;
    }

    /**
     * Provide a name for this plugin
     *
     * The name will appear in the plugins list where editors can
     * enable and disable plugins.
     */
    public function getDisplayName()
    {
        return 'PandocTransformatorSLUB';
    }

    /**
     * Provide a description for this plugin
     *
     * The description will appear in the plugins list where editors can
     * enable and disable plugins.
     */
    public function getDescription()
    {
        return __('plugins.generic.pandocTransformatorSLUB.displayName');
    }

    public function getLocaleFilename($locale)
    {
        return "plugins/generic/pandocTransformatorSLUB/locale/".$locale."/locale.po";
    }

    public function callbackLoadHandler($hookName, $args) {
        $page = $args[0];
        $op = $args[1];

        if ($page == "docx_to_pdf" && $op == "parse") {
            define('HANDLER_CLASS', 'TransformatorHandler');
            define('CONVERTER_PLUGIN_NAME', $this->getName());
            $args[2] = $this->getPluginPath() . '/' . 'PandocTransformatorHandler.inc.php';
        }

        return false;
    }

    public function showConversionButton($hookName, $args){
        /* @var $templateMgr TemplateManager */
        $templateMgr = $args[0];
        /* @var $template_path string */
        $template_path = $args[1];

        if ($template_path == 'controllers/grid/gridRow.tpl') {
            /* @var $row GridRow */
            $row = $templateMgr->getTemplateVars('row');

            $data = $row->getData();
            if (is_array($data) && (isset($data['submissionFile']))) {
                /* @var $submissionFile SubmissionFile*/
                $submissionFile = $data['submissionFile'];
                $submissionId = $submissionFile->getData('submissionId');
                $submissionFileName = $submissionFile->getData('name');
                $submissionPath = $submissionFile->getData('path');
            #error_log('SubmissionFileName: '.json_encode($submissionFileName));
            #error_log($submissionFile->getData('path'));
                $button_action = new RedirectAction('https://www.google.de');
                $request = $this->getRequest();

                $session = $request->getSession();
                $router = $request->getRouter();
                $path = $request->getDispatcher()->url($request, ROUTE_PAGE, null, 'docx_to_pdf', 'parse', null, array(
                    'filePath'=>$submissionPath,
                    'assocType' => $submissionFile->getData('assocType'),
                    'assocId' => $submissionFile->getData('assocId'),
                    'genreId' => $submissionFile->getData('genreId'),
                    'fileStage' => $submissionFile->getData('fileStage'),
                    'locale' => $submissionFile->getData('locale'),
                    'submissionId' => $submissionId
                ));
                $redirect_path = $request->getDispatcher()->url($request, ROUTE_PAGE, null, 'workflow', 'access', $submissionId);
                $button_action = new PostAndRedirectAction($path, $redirect_path);
                error_log("===".$path."===");
                #button_action = new AjaxAction(
                #   $router->url($request));
                $button =  new LinkAction(
                    'pandoc', $button_action// Ein eindeutiger ID-String für diese Aktion.
                    , // Die Aktion, die beim Klicken auf den Button ausgeführt wird.
                    __('plugins.generic.pandocTransformatorSLUB.test')
                );

                $row->addAction($button);
            }
            return false;
        }



        #if ($template_path !== 'workflow/workflow.tpl') {
        #    return false;
        #}


 #       error_log('==========================================>');

  #      /* @var $submission Submission */
        #$submission = $templateMgr->getTemplateVars('submission');
        #error_log($submission->getData('stageId'));
        #error_log(implode(':',$submission->getAllData()));

#        error_log('==========================================');



        return false;
    }



}
