<?php

declare(strict_types=1);

namespace App\Enums;

enum CompanyInfoKeys: string
{
    case ADDRESS_STREET = 'street';
    case ADDRESS_HOUSE_NUMBER = 'houseNumber';
    case ADDRESS_ORIENTATION_NUMBER = 'orientationNumber';
    case ADDRESS_ZIP = 'zip';
    case ADDRESS_CITY = 'city';

    case COMPANY_NAME = 'name';
    case COMPANY_ID = 'id';
    case COMPANY_COUNTRY_CODE = 'countryCode';
    case COMPANY_VAT_ID = 'vatId';
    case COMPANY_VAT_PAYER = 'vatPayer';
    case COMPANY_ADDRESS = 'address';
}
