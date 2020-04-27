<?php


namespace App\Controller;

use App\Entity\Tuut;
use App\Entity\User;
use App\Repository\TuutRepository;
use Doctrine\ORM\EntityManagerInterface;
use http\Exception\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * Class HomeController
 *
 * @package App\Controller
 */
class HomeController extends AbstractController
{
    /**
     * @var TuutRepository
     */
    private $tuutRepository;
    /**
     * @var Security
     */
    private $security;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var FlashBagInterface
     */
    private $flashBag;

    public function __construct(TuutRepository $tuutRepository, Security $security, EntityManagerInterface $entityManager, FlashBagInterface $flashBag)
    {
        $this->tuutRepository = $tuutRepository;
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->flashBag = $flashBag;
    }

    /**
     * @return Response
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        if ($this->security->isGranted('ROLE_RECEIVER')) {
            $pending = $this->tuutRepository
                ->createQueryBuilder('o')
                ->where('o.receiver IS NULL')
                ->orderBy('o.datetime', 'DESC')
                ->getQuery()
                ->getResult()
            ;
            $waiting = $this->tuutRepository
                ->createQueryBuilder('o')
                ->where('o.receiver = :user')
                ->andWhere('o.initiator IS NULL')
                ->setParameter('user', $this->security->getUser())
                ->orderBy('o.datetime', 'DESC')
                ->getQuery()
                ->getResult()
            ;
            $actions = [
                'Away' => 'away',
                'Missed' => 'missed',
            ];
        } else {
            $pending = $this->tuutRepository
                ->createQueryBuilder('o')
                ->where('o.initiator IS NULL')
                ->orderBy('o.datetime', 'DESC')
                ->getQuery()
                ->getResult()
            ;
            $waiting = $this->tuutRepository
                ->createQueryBuilder('o')
                ->where('o.initiator = :user')
                ->andWhere('o.receiver IS NULL')
                ->setParameter('user', $this->security->getUser())
                ->orderBy('o.datetime', 'DESC')
                ->getQuery()
                ->getResult()
            ;
            $actions = [
                'Reject' => 'reject',
                'Acknowledge' => 'acknowledge'
            ];
        }
        $history = $this->tuutRepository
            ->createQueryBuilder('o')
            ->where('o.status IN(:statuses)')
            ->setParameter('statuses', [
                Tuut::STATUS_ACKNOWLEDGED,
                Tuut::STATUS_REJECTED,
                Tuut::STATUS_MISSED,
                Tuut::STATUS_AWAY
            ])
            ->orderBy('o.datetime', 'DESC')
            ->setMaxResults(30)
            ->getQuery()
            ->getResult()
        ;
        return $this->render('tuuts/tuuts.twig', [
            'pendingTuuts' => $pending,
            'waitingTuuts' => $waiting,
            'historicalTuuts' => $history,
            'actions' => $actions,
        ]);
    }

    /**
     * @return Response
     * @Route("/create/tuut", name="create-tuut")
     */
    public function createTuut(): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if ($this->security->isGranted('ROLE_RECEIVER')) {
            $tuut = new Tuut();
            $tuut
                ->setReceiver($user)
                ->setStatus(Tuut::STATUS_INITIATED)
            ;
            $this->entityManager->persist($tuut);
            $this->entityManager->flush();
        } else {
            $tuut = new Tuut();
            $tuut
                ->setInitiator($user)
                ->setStatus(Tuut::STATUS_INITIATED)
            ;
            $this->entityManager->persist($tuut);
            $this->entityManager->flush();
        }
        $this->flashBag->add('success', 'Created tuut!');
        return $this->redirectToRoute('home');
    }

    private function getTuutById($tuutId): Tuut
    {
        $tuut = $this->tuutRepository->findOneBy([
            'id' => $tuutId
        ]);
        if (!$tuut) {
            throw new InvalidArgumentException('Ouch...');
        }
        return $tuut;
    }

    /**
     * @Route("/away?tuutId={tuutId}", name="away")
     */
    public function away($tuutId)
    {
        $this
            ->getTuutById($tuutId)
            ->setReceiver($this->security->getUser())
            ->setStatus(Tuut::STATUS_AWAY)
        ;
        $this->entityManager->flush();
        $this->flashBag->add('success', 'Registered!');
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/missed?tuutId={tuutId}", name="missed")
     */
    public function missed($tuutId)
    {
        $this
            ->getTuutById($tuutId)
            ->setReceiver($this->security->getUser())
            ->setStatus(Tuut::STATUS_MISSED)
        ;
        $this->entityManager->flush();
        $this->flashBag->add('success', 'Registered!');
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/reject?tuutId={tuutId}", name="reject")
     */
    public function reject($tuutId)
    {
        $this
            ->getTuutById($tuutId)
            ->setInitiator($this->security->getUser())
            ->setStatus(Tuut::STATUS_REJECTED)
        ;
        $this->entityManager->flush();
        $this->flashBag->add('success', 'Registered!');
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/acknowledge?tuutId={tuutId}", name="acknowledge")
     */
    public function acknowledge($tuutId)
    {
        $this
            ->getTuutById($tuutId)
            ->setInitiator($this->security->getUser())
            ->setStatus(Tuut::STATUS_ACKNOWLEDGED)
        ;
        $this->entityManager->flush();
        $this->flashBag->add('success', 'Registered!');
        return $this->redirectToRoute('home');
    }
}