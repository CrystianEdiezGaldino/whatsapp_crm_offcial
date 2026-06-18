<?php

namespace App\Database\Grammars;

use Illuminate\Database\Query\Grammars\SqlServerGrammar as BaseSqlServerGrammar;

class SqlServerQueryGrammar extends BaseSqlServerGrammar
{
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }
}
