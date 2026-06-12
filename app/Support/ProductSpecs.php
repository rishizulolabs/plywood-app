<?php

namespace App\Support;

class ProductSpecs
{
    public const THICKNESSES = ['4mm', '6mm', '9mm', '12mm', '16mm', '18mm', '19mm', '25mm'];

    public const SIZES = ['8ft x 4ft', '7ft x 4ft', '6ft x 4ft', '8ft x 3ft'];

    public const GRADES = ['MR', 'BWR', 'BWP', 'Commercial Ply', 'Marine Ply'];

    public const CORE_TYPES = ['Hardwood core', 'Softwood core', 'Poplar core', 'Combi core'];

    public const NUMBER_OF_PLIES = ['3 ply', '5 ply', '7 ply', '9 ply', '11 ply', '13 ply'];

    public const IS_STANDARDS = ['IS 303', 'IS 710', 'IS 1659'];

    public const BRANDS = [
        'CenturyPly', 'Greenply', 'Kitply', 'Sainik', 'Austin', 'Archid',
        'Greenlam', 'Action Tesa', 'Duroply', 'Other',
    ];

    public const WARRANTIES = ['7 years', '15 years', '25 years', 'Lifetime'];

    public const FINISH_SURFACES = [
        'One side polished',
        'Both side polished',
        'Unpolished',
        'Veneer-finished',
        'Laminated',
        'Raw',
    ];

    public const GLUE_TYPES = [
        'Phenol Formaldehyde (PF)',
        'Melamine Urea Formaldehyde (MUF)',
        'Urea Formaldehyde (UF)',
    ];

    public const APPLICATIONS = [
        'Furniture',
        'Kitchen cabinets',
        'Wardrobes',
        'Modular kitchen',
        'Doors',
        'Partitions',
        'False ceiling',
        'False flooring',
    ];

    public const UNITS = ['sheet', 'sqft', 'sqm'];
}
