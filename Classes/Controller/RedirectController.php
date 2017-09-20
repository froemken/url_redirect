<?php
namespace StefanFroemken\UrlRedirect\Controller;

/*
 * This file is part of the url_redirect project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use StefanFroemken\UrlRedirect\Domain\Model\Config;
use StefanFroemken\UrlRedirect\Domain\Repository\ConfigRepository;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class PreProcess
 *
 * @package StefanFroemken\UrlRedirect\Controller
 */
class RedirectController extends ActionController
{
    /**
     * Backend Template Container
     *
     * @var string
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    /**
     * BackendTemplateContainer
     *
     * @var BackendTemplateView
     */
    protected $view;

    /**
     * @var ConfigRepository
     */
    protected $configRepository;

    /**
     * @var int the current page id
     */
    protected $id = 0;

    /**
     * inject configRepository
     *
     * @param ConfigRepository $configRepository
     *
     * @return void
     */
    public function injectConfigRepository(ConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
    }

    /**
     * Initializes view
     *
     * @param ViewInterface $view The view to be initialized
     *
     * @return void
     */
    protected function initializeView(ViewInterface $view)
    {
        parent::initializeView($view);
        $this->registerDocHeaderButtons();
    }

    /**
     * Initialize action
     *
     * @return void
     */
    protected function initializeAction()
    {
        // determine id parameter
        $this->id = (int)GeneralUtility::_GP('id');
        if ($this->request->hasArgument('id')) {
            $this->id = (int)$this->request->getArgument('id');
        }
    }

    /**
     * Registers the Icons into the DocHeader
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function registerDocHeaderButtons()
    {
        /** @var ButtonBar $buttonBar */
        $buttonBar = $this->view->getModuleTemplate()->getDocHeaderComponent()->getButtonBar();

        $showRedirectsButton = $buttonBar->makeInputButton()
            ->setName('showRedirects')
            ->setValue('1')
            ->setTitle(LocalizationUtility::translate('showRedirects', 'urlRedirect'))
            ->setOnClick($this->createLink('list'))
            ->setIcon($this->view->getModuleTemplate()->getIconFactory()->getIcon(
                'actions-document-close',
                Icon::SIZE_SMALL
            ));

        $importRedirectsButton = $buttonBar->makeInputButton()
            ->setName('importRedirects')
            ->setValue('1')
            ->setTitle(LocalizationUtility::translate('importRedirects', 'urlRedirect'))
            ->setOnClick($this->createLink('importForm'))
            ->setIcon($this->view->getModuleTemplate()->getIconFactory()->getIcon(
                'actions-document-close',
                Icon::SIZE_SMALL
            ));

        $splitButton = $buttonBar->makeSplitButton()
            ->addItem($showRedirectsButton)
            ->addItem($importRedirectsButton);
        $buttonBar->addButton($splitButton);
    }

    /**
     * Create quoted link
     *
     * @param string $action
     *
     * @return string
     */
    protected function createLink($action)
    {
        return 'window.location.href=' . GeneralUtility::quoteJSvalue(
            $this->uriBuilder
                ->reset()
                ->setTargetPageUid($this->id)
                ->uriFor($action)
            );
    }

    /**
     * List action
     *
     * @return void
     */
    public function listAction()
    {
        $configurations = $this->configRepository->findAll();
        $this->view->assign('configurations', $configurations);
    }

    /**
     * New action
     *
     * @return void
     */
    public function newAction()
    {
        /** @var Config $config */
        $config = $this->objectManager->get(Config::class);
        $this->view->assign('config', $config);
        $this->view->assign('httpStatus', $this->configRepository->getHttpStatus());
        $this->view->assign('domains', $this->configRepository->getSysDomains());
    }

    /**
     * Create action
     *
     * @param Config $config
     *
     * @return void
     */
    public function createAction(Config $config)
    {
        $config->setPid(0);
        $this->configRepository->add($config);
        $this->addFlashMessage('Configuration was saved successfully');
        $this->redirect('list');
    }

    /**
     * Edit action
     *
     * @param Config $config
     *
     * @return void
     */
    public function editAction(Config $config)
    {
        $this->view->assign('config', $config);
        $this->view->assign('httpStatus', $this->configRepository->getHttpStatus());
        $this->view->assign('domains', $this->configRepository->getSysDomains());
    }

    /**
     * Update action
     *
     * @param Config $config
     *
     * @return void
     */
    public function updateAction(Config $config)
    {
        $config->setPid(0);
        $this->configRepository->update($config);
        $this->addFlashMessage('Configuration was updated successfully');
        $this->redirect('list');
    }

    /**
     * Delete action
     *
     * @param int $config
     *
     * @return void
     */
    public function deleteAction($config)
    {
        /** @var Config $configObject */
        $configObject = $this->configRepository->findByIdentifier((int)$config);
        $this->addFlashMessage(
            'Are you sure you want to delete this redirect record?',
            'Delete record',
            AbstractMessage::WARNING
        );
        $this->view->assign('config', $configObject);
    }

    /**
     * Do Delete action
     *
     * @param int $config
     *
     * @return void
     */
    public function doDeleteAction($config)
    {
        /** @var Config $configObject */
        $configObject = $this->configRepository->findByIdentifier((int)$config);
        $this->configRepository->remove($configObject);
        $this->addFlashMessage('Configuration has been removed');
        $this->redirect('list');
    }

    /**
     * Show import form
     *
     * @return void
     */
    public function importFormAction()
    {
        $this->view->assignMultiple([
            'separator' => ';',
            'quote' => '"',
            'escape' => '\\'
        ]);
    }

    /**
     * Import action
     *
     * @param array $csvFile
     * @param string $separator
     * @param string $quote
     * @param string $escape
     *
     * @return void
     */
    public function importAction(array $csvFile, $separator = ';', $quote = '"', $escape = '\'')
    {
        $allowedFileExt = ['csv', 'txt'];
        if ($csvFile['error']) {
            $this->addFlashMessage('Error while uploading file.', 'Error', FlashMessage::ERROR);
            $this->redirect('importForm');
        }

        $fileParts = GeneralUtility::split_fileref($csvFile['name']);
        if (!in_array($fileParts['fileext'], $allowedFileExt)) {
            $this->addFlashMessage('Invalid file format', 'Error', FlashMessage::ERROR);
            $this->redirect('importForm');
        }
        /** @var PersistenceManager $persistenceManager */
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $rows = file($csvFile['tmp_name']);
        foreach ($rows as $row) {
            list($requestUri, $targetUri, $httpStatus) = str_getcsv(
                $row,
                $separator,
                $quote,
                $escape
            );
            /** @var Config $config */
            $config = $this->objectManager->get(Config::class);
            $config->setPid(0);
            $config->setRequestUri($requestUri);
            $config->setTargetUri($targetUri);
            $config->setHttpStatus($httpStatus);
            $persistenceManager->add($config);
        }
        $persistenceManager->persistAll();
        $this->addFlashMessage('Import successful', 'Yeahhh', FlashMessage::OK);
        $this->redirect('list');
    }
}
