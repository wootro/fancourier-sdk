<?php

/**
 * This file was created by the developers from Infifni.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://infifnisoftware.ro and write us
 * an email on contact@infifnisoftware.ro.
 */

namespace Infifni\FanCourierApiClient\Request;

use Exception;
use Infifni\FanCourierApiClient\Exception\FanCourierInvalidParamException;

class GenerateAwb extends Endpoint implements CsvFileRequestInterface
{
    const API_SUBMIT_ROW_SUCCESSFUL = '1';

    protected $keys;

    /**
     * @return string
     */
    protected function getApiPath(): string
    {
        return 'import_awb_integrat.php';
    }

    /**
     * @return string
     */
    public function getApiResultType(): string
    {
        return EndpointInterface::API_RESULT_TYPE_PARSE;
    }

    /**
     *
     * @param string $result
     * @return array|string
     */
    public function parseResult(string $result): array
    {
        $parse = str_getcsv($result, "\n");
        if (empty($parse)) {
            return $result;
        }

        $returnResult = [];
        try {
            $requestParams = $this->getRequestParams();
            foreach ($parse as $value) {
                $resultPerRow = explode(',', $value);
                if (empty($resultPerRow[0]) && empty($resultPerRow[1]) && empty($resultPerRow[2])) {
                    continue;
                }
                $returnResult[] = [
                    'line' => (int) $resultPerRow[0],
                    'awb' => self::API_SUBMIT_ROW_SUCCESSFUL === $resultPerRow[1] ? $resultPerRow[2] : false,
                    'cost' => self::API_SUBMIT_ROW_SUCCESSFUL === $resultPerRow[1] ? $resultPerRow[3] : false,
                    'sent_params' => current(array_values($requestParams))[(int) $resultPerRow[0] - 1],
                    'error_message' => self::API_SUBMIT_ROW_SUCCESSFUL === $resultPerRow[1] ? '' : $resultPerRow[2]];
            }
        } catch (Exception $ex) {
            $returnResult[] = [
                'line' => 1,
                'awb' => false,
                'cost' => false,
                'sent_params' => false,
                'error_message' => $ex->getMessage()
            ];
        }

        return $returnResult;
    }

    /**
     *
     * @param array $params
     * @return boolean
     * @throws FanCourierInvalidParamException
     */
    public function validate(array $params): bool
    {
        // this is the array data that contains the details about what dispatches need to be imported
        // this will be converted into a CSV file
        // the data corresponds to the import AWBs model from FAN application, it can contain one or more dispatches
        if (empty($params['fisier']) || ! is_array($params['fisier'])) {
            throw new FanCourierInvalidParamException(
                "Must set a field 'fisier' containing multiple arrays."
            );
        }

        $serviceAllowedValues = self::SERVICE_ALLOWED_VALUES;
        unset($serviceAllowedValues['export']);
        foreach ($params['fisier'] as $serviceParams) {
            $serviceType = $serviceParams['tip_serviciu'] ?? null;
            if (
                empty($serviceType)
                ||
                ! in_array($serviceType, $serviceAllowedValues, true)
            ) {
                throw new FanCourierInvalidParamException(
                    "Must set a field 'tip_serviciu' with one of these values: " . implode(', ', $serviceAllowedValues)
                );
            }

            $this->validateAgainst($serviceParams, $this->getFieldRules());
        }

        return true;
    }

    /**
     *
     * @return array
     */
    private function getFieldRules(): array
    {
        $serviceAllowedValues = self::SERVICE_ALLOWED_VALUES;
        unset($serviceAllowedValues['export']);
        return [
            'tip_serviciu' => [
                'required' => true,
                'allowed_values' => $serviceAllowedValues
            ],
            'banca' => [
                'required' => false
            ],
            'iban' => [
                'required' => false
            ],
            'nr_plicuri' => [
                'required' => true
            ],
            'nr_colete' => [
                'required' => true
            ],
            'greutate' => [
                'required' => true
            ],
            'plata_expeditie' => [
                'required' => true
            ],
            'ramburs' => [
                'required' => false
            ],
            'plata_ramburs_la' => [
                'required' => true,
                'allowed_values' => [
                    self::RECIPIENT_ALLOWED_VALUE,
                    self::SENDER_ALLOWED_VALUE
                ]
            ],
            'valoare_declarata' => [
                'required' => false
            ],
            'persoana_contact_expeditor' => [
                'required' => false
            ],
            'observatii' => [
                'required' => false
            ],
            'continut' => [
                'required' => false
            ],
            'nume_destinatar' => [
                'required' => false
            ],
            'persoana_contact' => [
                'required' => false
            ],
            'telefon' => [
                'required' => false
            ],
            'fax' => [
                'required' => false
            ],
            'email' => [
                'required' => false
            ],
            'judet' => [
                'required' => false
            ],
            'localitate' => [
                'required' => false
            ],
            'strada' => [
                'required' => false
            ],
            'nr' => [
                'required' => false
            ],
            'cod_postal' => [
                'required' => false
            ],
            'bl' => [
                'required' => false
            ],
            'scara' => [
                'required' => false
            ],
            'etaj' => [
                'required' => false
            ],
            'apartament' => [
                'required' => false
            ],
            'inaltime_pachet' => [
                'required' => false
            ],
            'latime_pachet' => [
                'required' => false
            ],
            'lungime_pachet' => [
                'required' => false
            ],
            'restituire' => [
                'required' => false
            ],
            'centru_cost' => [
                'required' => false
            ],
            'optiuni' => [
                'required' => false
            ],
            'packing' => [
                'required' => false
            ],
            'date_personale' => [
                'required' => false
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getCsvHeaders(): array
    {
        return [
            'expeditor_nume' => 'expeditor_nume',
            'expeditor_persoana_contact' => 'expeditor_persoana_contact', 
            'expeditor_telefon' => 'expeditor_telefon', 
            'expeditor_fax' => 'expeditor_fax', 
            'expeditor_email' => 'expeditor_email', 
            'expeditor_judet' => 'expeditor_judet', 
            'expeditor_localitatea' => 'expeditor_localitatea', 
            'expeditor_strada' => 'expeditor_strada', 
            'expeditor_nr' => 'expeditor_nr', 
            'expeditor_cod postal' => 'expeditor_cod postal', 
            'expeditor_bloc' => 'expeditor_bloc', 
            'expeditor_scara' => 'expeditor_scara', 
            'expeditor_etaj' => 'expeditor_etaj', 
            'expeditor_apartament' => 'expeditor_apartament', 
            'destinatar_nume' => 'destinatar_nume', 
            'destinatar_persoana_contact' => 'destinatar_persoana_contact', 
            'destinatar_telefon' => 'destinatar_telefon', 
            'destinatar_fax' => 'destinatar_fax', 
            'destinatar_email' => 'destinatar_email', 
            'destinatar_judet' => 'destinatar_judet', 
            'destinatar_localitatea' => 'destinatar_localitatea', 
            'destinatar_strada' => 'destinatar_strada', 
            'destinatar_nr' => 'destinatar_nr', 
            'destinatar_cod postal' => 'destinatar_cod', 
            'destinatar_bloc' => 'destinatar_bloc', 
            'destinatar_scara' => 'destinatar_scara', 
            'destinatar_etaj' => 'destinatar_etaj', 
            'destinatar_apartament' => 'destinatar_apartament',
            'tip_serviciu' => 'tip_serviciu',
            'banca' => 'banca',
            'iban' => 'IBAN',
            'nr_plicuri' => 'nr_plicuri',
            'nr_colete' => 'nr_colete',
            'greutate' => 'greutate',
            'plata_expeditie' => 'plata_expeditie',
            'ramburs' => 'ramburs',
            'plata_ramburs_la' => 'plata_ramburs_la',
            'valoare_declarata' => 'valoare_declarata',
            'observatii' => 'observatii',
            'continut' => 'continut',
            'inaltime_pachet' => 'inaltime_pachet',
            'latime_pachet' => 'latime_pachet',
            'lungime_pachet' => 'lungime_pachet',
            'restituire' => 'restituire',
            'centru_cost' => 'centru_cost',
            'awb_retur' => 'awb_retur',
            'optiuni' => 'optiuni',
            'packing' => 'packing',
            'date personala packing' => 'date personala packing'
        ];
    }
}

