<?php

namespace Oro\Bundle\CampaignBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class CampaignRepository extends EntityRepository
{
    /**
     * @param AclHelper $aclHelper
     * @param int       $recordsCount
     * @param array     $dateRange
     *
     * @return array
     */
    public function getCampaignsLeads(AclHelper $aclHelper, $recordsCount, $dateRange = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('campaign.name as label', 'COUNT(lead.id) as number', 'MAX(campaign.createdAt) as maxCreated')
            ->from('OroCampaignBundle:Campaign', 'campaign')
            ->leftJoin('OroSalesBundle:Lead', 'lead', 'WITH', 'lead.campaign = campaign')
            ->orderBy('maxCreated', 'DESC')
            ->groupBy('campaign.name')
            ->setMaxResults($recordsCount);

        if ($dateRange) {
            $qb->where($qb->expr()->between('lead.createdAt', ':dateFrom', ':dateTo'))
                ->setParameter('dateFrom', $dateRange['start'])
                ->setParameter('dateTo', $dateRange['end']);
        }

        return $aclHelper->apply($qb)->getArrayResult();
    }

    /**
     * @param string $leadAlias
     *
     * @return QueryBuilder
     */
    public function getCampaignsLeadsQB($leadAlias)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select(
            'campaign.name as label',
            sprintf('COUNT(%s.id) as number', $leadAlias),
            'MAX(campaign.createdAt) as maxCreated'
        )
            ->from('OroCampaignBundle:Campaign', 'campaign')
            ->leftJoin('OroSalesBundle:Lead', $leadAlias, 'WITH', sprintf('%s.campaign = campaign', $leadAlias))
            ->orderBy('maxCreated', 'DESC')
            ->groupBy('campaign.name');

        return $qb;
    }

    /**
     * @param AclHelper $aclHelper
     * @param int       $recordsCount
     * @param array     $dateRange
     *
     * @return array
     */
    public function getCampaignsOpportunities(AclHelper $aclHelper, $recordsCount, $dateRange = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('campaign.name as label', 'COUNT(opportunities.id) as number')
            ->from('OroCampaignBundle:Campaign', 'campaign')
            ->join('OroSalesBundle:Lead', 'lead', 'WITH', 'lead.campaign = campaign')
            ->join('lead.opportunities', 'opportunities')
            ->orderBy('number', 'DESC')
            ->groupBy('campaign.name')
            ->setMaxResults($recordsCount);

        if ($dateRange) {
            $qb->where($qb->expr()->between('opportunities.createdAt', ':dateFrom', ':dateTo'))
                ->setParameter('dateFrom', $dateRange['start'])
                ->setParameter('dateTo', $dateRange['end']);
        }

        return $aclHelper->apply($qb)->getArrayResult();
    }

    /**
     * @param string $opportunitiesAlias
     *
     * @return QueryBuilder
     */
    public function getCampaignsOpportunitiesQB($opportunitiesAlias)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('campaign.name as label', sprintf('COUNT(%s.id) as number', $opportunitiesAlias))
            ->from('OroCampaignBundle:Campaign', 'campaign')
            ->join('OroSalesBundle:Lead', 'lead', 'WITH', 'lead.campaign = campaign')
            ->join('lead.opportunities', $opportunitiesAlias)
            ->orderBy('number', 'DESC')
            ->groupBy('campaign.name');

        return $qb;
    }

    /**
     * @param AclHelper $aclHelper
     * @param int       $recordsCount
     * @param array     $dateRange
     *
     * @return array
     */
    public function getCampaignsByCloseRevenue(AclHelper $aclHelper, $recordsCount, $dateRange = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select(
                'campaign.name as label',
                'SUM(CASE WHEN (opp.status=\'won\') THEN opp.closeRevenueValue ELSE 0 END) as closeRevenue'
            )
            ->from('OroCampaignBundle:Campaign', 'campaign')
            ->join('OroSalesBundle:Lead', 'lead', 'WITH', 'lead.campaign = campaign')
            ->join('lead.opportunities', 'opp')
            ->orderBy('closeRevenue', 'DESC')
            ->groupBy('campaign.name')
            ->setMaxResults($recordsCount);

        if ($dateRange) {
            $qb->where($qb->expr()->between('opp.createdAt', ':dateFrom', ':dateTo'))
                ->setParameter('dateFrom', $dateRange['start'])
                ->setParameter('dateTo', $dateRange['end']);
        }

        return $aclHelper->apply($qb)->getArrayResult();
    }

    /**
     * @param string $opportunitiesAlias
     * @param CurrencyQueryBuilderTransformerInterface $qbTransformer
     *
     * @return QueryBuilder
     */
    public function getCampaignsByCloseRevenueQB(
        $opportunitiesAlias,
        CurrencyQueryBuilderTransformerInterface $qbTransformer
    ) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $crSelect = $qbTransformer->getTransformSelectQuery('closeRevenue', $qb, $opportunitiesAlias);
        $qb
            ->select(
                'campaign.name as label',
                sprintf(
                    'SUM(CASE WHEN (%s.status=\'won\') THEN %s ELSE 0 END) as closeRevenue',
                    $opportunitiesAlias,
                    $crSelect
                )
            )
            ->from('OroCampaignBundle:Campaign', 'campaign')
            ->join('OroSalesBundle:Lead', 'lead', 'WITH', 'lead.campaign = campaign')
            ->join('lead.opportunities', $opportunitiesAlias)
            ->where(sprintf('%s.status=\'won\'', $opportunitiesAlias))
            ->orderBy('closeRevenue', 'DESC')
            ->groupBy('campaign.name');

        return $qb;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }
}
