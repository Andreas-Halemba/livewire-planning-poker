<?php

namespace App\Enums;

enum IssueStatus: string
{
    case NEW = 'open';
    case VOTING = 'voting';
    case FINISHED = 'finished';
}
