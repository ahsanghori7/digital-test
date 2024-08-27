<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{
    /**
     * @var BookingRepository
     */
    protected $repository;

    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $response = $this->handleIndexRequest($request);

        return $this->createResponse($response);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $job = $this->repository->with('translatorJobRel.user')->find($id);

        return $this->createResponse($job);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->store($request->__authenticatedUser, $data);

        return $this->createResponse($response);
    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update($id, Request $request)
    {
        $data = $request->except(['_token', 'submit']);
        $response = $this->repository->updateJob($id, $data, $request->__authenticatedUser);

        return $this->createResponse($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->storeJobEmail($data);

        return $this->createResponse($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {
        $response = $this->handleHistoryRequest($request);

        return $this->createResponse($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->acceptJob($data, $request->__authenticatedUser);

        return $this->createResponse($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJobWithId(Request $request)
    {
        $jobId = $request->get('job_id');
        $response = $this->repository->acceptJobWithId($jobId, $request->__authenticatedUser);

        return $this->createResponse($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->cancelJobAjax($data, $request->__authenticatedUser);

        return $this->createResponse($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->endJob($data);

        return $this->createResponse($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function customerNotCall(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->customerNotCall($data);

        return $this->createResponse($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs(Request $request)
    {
        $response = $this->repository->getPotentialJobs($request->__authenticatedUser);

        return $this->createResponse($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function distanceFeed(Request $request)
    {
        $data = $request->all();

        $this->updateDistance($data);
        $this->updateJob($data);

        return $this->createResponse('Record updated!');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function reopen(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->reopen($data);

        return $this->createResponse($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function resendNotifications(Request $request)
    {
        $job = $this->repository->find($request->get('jobid'));
        $jobData = $this->repository->jobToData($job);
        $this->repository->sendNotificationTranslator($job, $jobData, '*');

        return $this->createResponse(['success' => 'Push sent']);
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        $job = $this->repository->find($request->get('jobid'));

        try {
            $this->repository->sendSMSNotificationToTranslator($job);
            return $this->createResponse(['success' => 'SMS sent']);
        } catch (\Exception $e) {
            return $this->createResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle index request logic.
     *
     * @param Request $request
     * @return mixed
     */
    private function handleIndexRequest(Request $request)
    {
        if ($userId = $request->get('user_id')) {
            return $this->repository->getUsersJobs($userId);
        }

        if ($this->isAdmin($request->__authenticatedUser)) {
            return $this->repository->getAll($request);
        }

        return null;
    }

    /**
     * Handle history request logic.
     *
     * @param Request $request
     * @return mixed
     */
    private function handleHistoryRequest(Request $request)
    {
        if ($userId = $request->get('user_id')) {
            return $this->repository->getUsersJobsHistory($userId, $request);
        }

        return null;
    }

    /**
     * Create a standardized response.
     *
     * @param mixed $data
     * @param int $status
     * @return \Illuminate\Http\Response
     */
    private function createResponse($data, $status = 200)
    {
        return response($data, $status);
    }

    /**
     * Check if the user is an admin or super admin.
     *
     * @param $user
     * @return bool
     */
    private function isAdmin($user)
    {
        return $user->user_type == env('ADMIN_ROLE_ID') || $user->user_type == env('SUPERADMIN_ROLE_ID');
    }

    /**
     * Update distance data.
     *
     * @param array $data
     */
    private function updateDistance(array $data)
    {
        if (!empty($data['distance']) || !empty($data['time'])) {
            Distance::where('job_id', $data['jobid'])->update([
                'distance' => $data['distance'] ?? '',
                'time' => $data['time'] ?? '',
            ]);
        }
    }

    /**
     * Update job data.
     *
     * @param array $data
     */
    private function updateJob(array $data)
    {
        $updateData = [
            'admin_comments' => $data['admincomment'] ?? '',
            'flagged' => $data['flagged'] == 'true' ? 'yes' : 'no',
            'session_time' => $data['session_time'] ?? '',
            'manually_handled' => $data['manually_handled'] == 'true' ? 'yes' : 'no',
            'by_admin' => $data['by_admin'] == 'true' ? 'yes' : 'no',
        ];

        if ($data['flagged'] == 'true' && empty($data['admincomment'])) {
            return response('Please, add comment', 400);
        }

        Job::where('id', $data['jobid'])->update($updateData);
    }
}
