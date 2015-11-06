<?php

$client = new Client();

$query = new NegotiationQuery($client);
$negotiations = $query
        ->page(1)
        ->perPage(20)
        ->addWhere('vacancy_id', $vacancyId)
        ->all();

$query = new ResumeQuery($client);
$resume = $query
        ->uri($resumeUri)
        ->one();

$query = new VacancyQuery($client);
$vacancy = $query->one($vacancyId);