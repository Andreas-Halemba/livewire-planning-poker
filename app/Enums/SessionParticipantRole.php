<?php

namespace App\Enums;

enum SessionParticipantRole: string
{
    case Voter = 'voter';
    case Viewer = 'viewer';
}
