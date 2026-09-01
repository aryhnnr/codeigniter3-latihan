<?php

// taruh di helper, misal application/helpers/ticket_helper.php
function status_badge_class($status){
    switch ($status) {
        case 'OPEN':         return 'badge-success';
        case 'IN PROGRESS':  return 'badge-warning';
        case 'DONE':         return 'badge-primary';
        case 'CANCELLED':    return 'badge-danger';
        default:              return 'badge-secondary';
    }
}

function prioritas_badge_class($prioritas){
    switch ($prioritas) {
        case 'Low':     return 'badge-secondary';
        case 'Normal':  return 'badge-info';
        case 'High':    return 'badge-warning';
        case 'Urgent':  return 'badge-danger';
        default:         return 'badge-secondary';
    }
}