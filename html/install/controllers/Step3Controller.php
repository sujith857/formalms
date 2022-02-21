<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

require_once dirname(__FILE__) . '/StepController.php';

class Step3Controller extends StepController
{
    public $step = 3;

    public function validate()
    {
        $agree = Get::pReq('agree', DOTY_INT, 0);
        if ($agree != 1 && !isset($_SESSION['license_accepted'])) {
            return false;
        } else {
            $_SESSION['license_accepted'] = 1;

            return true;
        }
    }
}
