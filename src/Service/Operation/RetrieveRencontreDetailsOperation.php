<?php declare(strict_types=1);

namespace Alamirault\FFTTApi\Service\Operation;

use Alamirault\FFTTApi\Exception\InvalidLienRencontreException;
use Alamirault\FFTTApi\Model\Factory\RencontreDetailsFactory;
use Alamirault\FFTTApi\Model\Rencontre\RencontreDetails;
use Alamirault\FFTTApi\Service\FFTTClientInterface;

final class RetrieveRencontreDetailsOperation
{
    public function __construct(
        private readonly FFTTClientInterface $client,
        private readonly RencontreDetailsFactory $rencontreDetailsFactory,
    ) {}

    /**
     * @throws \Exception
     */
    public function retrieveRencontreDetailsByLien(string $lienRencontre, ?string $clubEquipeA, ?string $clubEquipeB): RencontreDetails
    {
        $data = $this->client->get('xml_chp_renc', [], $lienRencontre);
        if (!(isset($data['resultat']) && isset($data['joueur']) && isset($data['partie']))) {
            throw new InvalidLienRencontreException($lienRencontre);
        }

        parse_str($lienRencontre, $params);
        $clubEquipeA = array_key_exists('clubnum_1', $params) ? strval($params['clubnum_1']) : ($clubEquipeA ?? '');
        $clubEquipeB = array_key_exists('clubnum_2', $params) ? strval($params['clubnum_2']) : ($clubEquipeB ?? '');

        if (0 === strlen($clubEquipeA)) {
            throw new \Exception("Parameter 'clubnum_1' not given");
        }
        if (0 === strlen($clubEquipeB)) {
            throw new \Exception("Parameter 'clubnum_2' not given");
        }

        return $this->rencontreDetailsFactory->createFromArray($data, $clubEquipeA ?? $clubEquipeA, $clubEquipeB);
    }
}
