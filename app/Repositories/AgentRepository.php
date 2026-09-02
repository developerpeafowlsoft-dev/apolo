<?php
namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\Agent;

class AgentRepository extends Repository
{
    public static function model()
    {
        return Agent::class;    
    }
}