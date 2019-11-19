<?php

namespace CurtainCallWP\Helpers;

class QueryHelper
{
    /**
     * @param string $type
     * @param string $clause
     * @return string
     */
    public static function whereCCWPJoinType(string $type = 'both', string $clause = 'WHERE'): string
    {
        $query = "    ";
        
        switch($type) {
            case 'cast':
                $query .= $clause . " `ccwp_join`.`type` = 'cast'";
                break;
            case 'crew':
                $query .= $clause . " `ccwp_join`.`type` = 'crew'";
                break;
            case 'both':
            default:
                $query .= $clause . " (`ccwp_join`.`type` = 'cast' OR `ccwp_join`.`type` = 'crew')";
                break;
        };
        
        $query .= PHP_EOL;
        
        return $query;
    }
}
