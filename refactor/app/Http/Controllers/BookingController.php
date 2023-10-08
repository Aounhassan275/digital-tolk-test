<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Http\Requests;
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
     * 
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

     /**
     * Method Index
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $user= User::find($request->user_id);

        if ($user->id) {
            $response = $this->getUserJobs($user->id);
        } elseif ($user->isAdmin() || $user->isSuperAdmin()) {
            $response = $this->getAllJobs($request);
        } else {
            $response = [];
        }
    
        return response($response);
    }
    
    /**
     * Method getUserJobs
     *
     * @param $user_id
     * @return 
     */
    protected function getUserJobs($id)
    {
        return $this->repository->getUsersJobs($id);
    }
    
    /**
     * Method getAllJobs
     *
     * @param $request
     * @return 
     */
    protected function getAllJobs($request)
    {
        return $this->repository->getAll($request);
    }
    
    /**
     * Show Data
     *
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $job = $this->retrieveJobWithRelatedData($id);

        return response($job);
    }

    /**
     * Method Retrieve Job With Related Data
     *
     * @param $id
     * @return mixed
     */
    private function retrieveJobWithRelatedData($id)
    {
        return $this->repository
            ->with('translatorJobRel.user')
            ->find($id);
    }

    /**
     *Method Store Data
     *
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $authenticatedUser = $request->__authenticatedUser;
    
        $response = $this->repository->store($authenticatedUser, $data);
    
        return response($response);

    }

    /**
     *Method Update Data
     *
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update($id, Request $request)
    {
        
        $data = $request->all();
        $cuser = $request->__authenticatedUser;
        $response = $this->repository->updateJob($id, array_except($data, ['_token', 'submit']), $cuser);

        return response($response);
    }

     /**
     *Method Immediate Job Email
     *
     * @param Request $request
     * @return mixed
     */

    public function immediateJobEmail(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->storeJobEmail($data);
    
        return response($response);
    }

    /**
     *Method Get History
     *
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {
        if ($request->user_id) {
            $response = $this->repository->getUsersJobsHistory($user_id, $request);
            return response($response);
        }
    
        return null;
    }

    /**
     *Method Accept Job
     *
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->repository->acceptJob($data, $user);

        return response($response);
    }

    /**
     *Method Accept Job With Id
     *
     * @param Request $request
     * @return mixed
     */
    public function acceptJobWithId(Request $request)
    {
        $data = $request->get('job_id');
        $user = $request->__authenticatedUser;

        $response = $this->repository->acceptJobWithId($data, $user);

        return response($response);
    }

   /**
     *Method Cancel Job
     *
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->repository->cancelJobAjax($data, $user);

        return response($response);
    }

    /**
     *Method End Of Job
     *
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request)
    {
        $data = $request->all();

        $response = $this->repository->endJob($data);

        return response($response);

    }

    /**
     *Method Customer Not Call
     *
     * @param Request $request
     * @return mixed
     */
    public function customerNotCall(Request $request)
    {
        $data = $request->all();

        $response = $this->repository->customerNotCall($data);

        return response($response);

    }

    /**
     *Method GetPotentialJobs
     *
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->repository->getPotentialJobs($user);

        return response($response);
    }

    /**
     *Method Distance Feed
     *
     * @param Request $request
     * @return mixed
     */
    public function distanceFeed(Request $request)
    {
        $data = $request->all();
        $jobId = $data['jobid'] ?? null;
        
        $distance = $data['distance'] ?? '';
        $time = $data['time'] ?? '';
        $session = $data['session_time'] ?? '';
        $admincomment = $data['admincomment'] ?? '';
        
        $flagged = ($data['flagged'] == 'true' && $admincomment != '') ? 'yes' : 'no';
        $manually_handled = ($data['manually_handled'] == 'true') ? 'yes' : 'no';
        $by_admin = ($data['by_admin'] == 'true') ? 'yes' : 'no';
        
        if ($time || $distance) {
            Distance::where('job_id', '=', $jobId)->update(['distance' => $distance, 'time' => $time]);
        }
        
        if ($admincomment || $session || $flagged || $manually_handled || $by_admin) {
            Job::where('id', '=', $jobId)->update([
                'admin_comments' => $admincomment,
                'flagged' => $flagged,
                'session_time' => $session,
                'manually_handled' => $manually_handled,
                'by_admin' => $by_admin
            ]);
        }
        
        return response('Record updated!');
    }

    /**
     *Method Reopen
     *
     * @param Request $request
     * @return mixed
     */
    public function reopen(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->reopen($data);

        return response($response);
    }
    
    /**
     *Method Resend Notifications
     *
     * @param Request $request
     * @return mixed
     */
    public function resendNotifications(Request $request)
    {
        $data = $request->all();
        $jobId = $data['jobid'];
        
        $job = $this->repository->find($jobId);
        $jobData = $this->repository->jobToData($job);
        
        $this->repository->sendNotificationTranslator($job, $jobData, '*');
    
        return response(['success' => 'Push sent']);
    }

    /**
     *Method Sends SMS to Translator
     * 
     * @param Request $request
     * @return 
     */
    public function resendSMSNotifications(Request $request)
    {
        $data = $request->all();
        $jobId = $data['jobid'];
        
        $job = $this->repository->find($jobId);
        $jobData = $this->repository->jobToData($job);
    
        try {
            $this->repository->sendSMSNotificationToTranslator($job);
            return response(['message' => 'SMS notification sent successfully']);
        } catch (\Exception $e) {
            return response(['error' => 'Failed to send SMS notification', 'message' => $e->getMessage()], 500);
        }
    }

}
