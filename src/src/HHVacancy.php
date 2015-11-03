<?php

class HHVacancy {

    const IBLOCK_CITY_ID = 18;
    const IBLOCK_DIRECTION_ID = 15;
    const IBLOCK_EXPERIENCE_ID = 35;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $description;

    /**
     * @var int
     */
    public $experience = null;

    /**
     * @var string
     */
    public $schedule = '';

    /**
     * @var string
     */
    public $employment = '';

    /**
     * @var int
     */
    public $area;

    /**
     * @var string
     */
    public $type = 'open';

    /**
     * @var int
     */
    public $employer = null;

    /**
     * @var int[]
     */
    public $specialization;

    /**
     * @var int
     */
    public $salary_from = 0;

    /**
     * @var int
     */
    public $salary_to = 0;

    /**
     * @var string
     */
    public $salary_currency = null;

    /**
     * @var string
     */
    public $billing_type;

    /**
     * @var string
     */
    public $site = 'hh';

    /**
     * @var int
     */
    public $address_id = null;
    private static $_required = array('name', 'description', 'specialization', 'type', 'billing_type', 'site', 'area');

    public function __construct(array $data = array()) {
        $properties = get_class_vars(get_class($this));

        foreach ($data as $key => $value) {
            if (array_key_exists($key, $properties)) {
                $this->$key = $value;
            }
        }
    }

    public static function validateVacancy(HHVacancy $vacancy) {
        $errors = array();
        foreach (self::$_required as $property) {

            if (empty($vacancy->$property)) {
                $errors[] = "Empty required property {$property}";
            }
        }
        if (strlen($vacancy->description) < 200) {
            $errors[] = "Description's length cannot be less then 200 symbols";
        }
        if ((!empty($vacancy->salary_from) || !empty($vacancy->salary_to)) && empty($vacancy->salary_currency)) {
            $errors[] = "Salary currency not specified";
        }
        return $errors;
    }

    public static function toJSON(HHVacancy $vacancy, $forChange = false) {
        $hhVacancy = array();
        if ($forChange === false) {
            $errors = self::validateVacancy($vacancy);
            if (!empty($errors)) {
                throw new HHVacancyException($errors);
            }
            $hhVacancy['site'] = array('id' => $vacancy->site);
            $hhVacancy['type'] = array('id' => $vacancy->type);
            $hhVacancy['billing_type'] = array('id' => $vacancy->billing_type);
            $hhVacancy['area'] = array('id' => self::_getAreaId($vacancy->area));
        }

        $hhVacancy['name'] = $vacancy->name;
        $hhVacancy['description'] = $vacancy->description;
        $hhVacancy['specializations'] = array();

        // $hhVacancy['specializations'] = array(array('id' => self::_getSpecializationId($vacancy->specialization)));
        $specializations = array();
        if (is_array($vacancy->specialization)) {
            $specializations = $vacancy->specialization;
        } else if ($vacancy->specialization instanceof stdClass) {
            if (is_array($vacancy->specialization->item)) {
                $specializations = $vacancy->specialization->item;
            } else {
                $specializations = array($vacancy->specialization->item);
            }
        }
        foreach ($specializations as $specialization) {
            $hhVacancy['specializations'][] = array('id' => self::_getSpecializationId($specialization));
        }

        if (!empty($vacancy->experience)) {
            $hhVacancy['experience'] = array('id' => self::_getExperienceId($vacancy->experience));
        }
        if (!empty($vacancy->schedule)) {
            $hhVacancy['schedule'] = array('id' => $vacancy->schedule);
        }
        if (!empty($vacancy->employment)) {
            $hhVacancy['employment'] = array('id' => $vacancy->employment);
        }
        if (!empty($vacancy->address_id)) {
            $hhVacancy['address'] = array('id' => $vacancy->address_id);
        }

        $salaryTo = intval($vacancy->salary_to);
        $salaryFrom = intval($vacancy->salary_from);
        HHLog::instance()->log('Salary from ' . $salaryFrom . ' ' . $vacancy->salary_from . ' ' . floatval($vacancy->salary_from));
        HHLog::instance()->log('Salary to ' . $salaryTo);
        HHLog::instance()->log(json_encode($vacancy));
        if (!empty($salaryFrom)) {
            $hhVacancy['salary'] = array(
                'from' => $salaryFrom,
            );
        }

        if (!empty($salaryTo)) {
            if (empty($hhVacancy['salary'])) {
                $hhVacancy['salary'] = array();
            }
            $hhVacancy['salary']['to'] = $salaryTo;
        }
        if (!empty($hhVacancy['salary'])) {
            $hhVacancy['salary']['currency'] = $vacancy->salary_currency;
        }
        $hhVacancy['allow_messages'] = false;
        return json_encode($hhVacancy);
    }

    private static function _getExperienceId($iBlockId) {
        $arFilter = array(
            'IBLOCK_ID' => self::IBLOCK_EXPERIENCE_ID,
            'ID' => $iBlockId,
        );
        $arSelect = array('ID', 'IBLOCK_ID', 'PROPERTY_HH_ID');
        $arNavStartParams = array(
            'nTopCount' => 1,
        );
        $dbResult = CIBlockElement::GetList(array(), $arFilter, false, $arNavStartParams, $arSelect);
        $arResult = $dbResult->GetNext();
        if (empty($arResult)) {
            return false;
        }
        if (empty($arResult['PROPERTY_HH_ID_VALUE'])) {
            return null;
        }
        return $arResult['PROPERTY_HH_ID_VALUE'];
    }

    private static function _getSpecializationId($iBlockId) {
        $arFilter = array(
            'IBLOCK_ID' => self::IBLOCK_DIRECTION_ID,
            'ID' => $iBlockId,
        );
        $arSelect = array('ID', 'IBLOCK_ID', 'UF_HH_DIRECTION_ID');
        $arNavStartParams = array(
            'nTopCount' => 1,
        );
        $dbResult = CIBlockSection::GetList(array(), $arFilter, false, $arSelect, $arNavStartParams);
        $arResult = $dbResult->GetNext();

        if (empty($arResult)) {
            throw new HHException("Error quering database.");
        }
        if (empty($arResult['UF_HH_DIRECTION_ID'])) {
            throw new HHVacancyException("HH specialization id not found for " . $iBlockId);
        }
        return (string) $arResult['UF_HH_DIRECTION_ID'];
    }

    private static function _getAreaId($iBlockId) {
        $arFilter = array(
            'IBLOCK_ID' => self::IBLOCK_CITY_ID,
            'ID' => $iBlockId,
        );
        $arSelect = array('ID', 'IBLOCK_ID', 'UF_HH_CITY_ID');
        $arNavStartParams = array(
            'nTopCount' => 1,
        );
        $dbResult = CIBlockSection::GetList(array(), $arFilter, false, $arSelect, $arNavStartParams);
        $arResult = $dbResult->GetNext();

        if (empty($arResult)) {
            throw new HHException("Error quering database.");
        }
        if (empty($arResult['UF_HH_CITY_ID'])) {
            throw new HHVacancyException("HH area id not found for " . $iBlockId);
        }
        return (string) $arResult['UF_HH_CITY_ID'];
    }

}
