<?php


defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */


/**
 * Class DashboardBlockCertificatesLms
 */
class DashboardBlockCertificatesLms extends DashboardBlockLms
{

    public function __construct($jsonConfig)
    {
        parent::__construct($jsonConfig);
	}

    public function parseConfig($jsonConfig) {

    }

    public function getAvailableTypesForBlock(): array
    {
        return [
            DashboardBlockLms::TYPE_1COL,
            DashboardBlockLms::TYPE_2COL,
            DashboardBlockLms::TYPE_3COL,
            DashboardBlockLms::TYPE_4COL
        ];
    }


    public function getViewData(): array
	{
		$data = $this->getCommonViewData();
		$data['certifcates'] = $this->getCertificates();
		return $data;
	}

	/**
	 * @return string
	 */
	public function getViewPath(): string
	{
		return $this->viewPath;
	}

	/**
	 * @return string
	 */
	public function getViewFile(): string
	{
		return $this->viewFile;
	}

	public function getLink(): string
	{
		return 'index.php?r=lms/mycertificate/show';
	}

	public function getRegisteredActions(): array
	{
		return [];
	}

	private function getCertificates(){
		$data = [];

		return $data;
	}




}
