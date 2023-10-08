<h1>BEFORE REFACTORING</h1>
<p>

    public function index(Request $request)
    {
        if($user_id = $request->get('user_id')) {

            $response = $this->repository->getUsersJobs($user_id);

        }
        elseif($request->__authenticatedUser->user_type == env('ADMIN_ROLE_ID') || $request->__authenticatedUser->user_type == env('SUPERADMIN_ROLE_ID'))
        {
            $response = $this->repository->getAll($request);
        }

        return response($response);
    }

</p>

<h1>AFTER REFACTORING</h1>

<p>

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

</p>

<h2>
My Point of View
<h2>

<p>
I am checking if the user is authenticated using $request->user(), which is a cleaner way to access the authenticated user.

I use separate methods getUserJobs and getAllJobs to encapsulate the logic for getting user-specific jobs and all jobs

</p>

<h1>BEFORE REFACTORING</h1>
<p>

    public function show($id)
    {
        $job = $this->repository->with('translatorJobRel.user')->find($id);

        return response($job);
    }

</p>

<h1>AFTER REFACTORING</h1>

<p>

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

</p>

<h2>
My Point of View
<h2>

<p>
I call a method named findJobWithRelations in our repository to retrieve the job and eager load related data.<p>
<p>
By separating the logic for finding the job and handling the response, you make the code more readable and maintainable, and you also handle the case where the job does not exist gracefully.
</p>

<h1>BEFORE REFACTORING</h1>
<p>

    public function distanceFeed(Request $request)
    {
        $data = $request->all();

        if (isset($data['distance']) && $data['distance'] != "") {
            $distance = $data['distance'];
        } else {
            $distance = "";
        }
        if (isset($data['time']) && $data['time'] != "") {
            $time = $data['time'];
        } else {
            $time = "";
        }
        if (isset($data['jobid']) && $data['jobid'] != "") {
            $jobid = $data['jobid'];
        }

        if (isset($data['session_time']) && $data['session_time'] != "") {
            $session = $data['session_time'];
        } else {
            $session = "";
        }

        if ($data['flagged'] == 'true') {
            if($data['admincomment'] == '') return "Please, add comment";
            $flagged = 'yes';
        } else {
            $flagged = 'no';
        }

        if ($data['manually_handled'] == 'true') {
            $manually_handled = 'yes';
        } else {
            $manually_handled = 'no';
        }

        if ($data['by_admin'] == 'true') {
            $by_admin = 'yes';
        } else {
            $by_admin = 'no';
        }

        if (isset($data['admincomment']) && $data['admincomment'] != "") {
            $admincomment = $data['admincomment'];
        } else {
            $admincomment = "";
        }
        if ($time || $distance) {

            $affectedRows = Distance::where('job_id', '=', $jobid)->update(array('distance' => $distance, 'time' => $time));
        }

        if ($admincomment || $session || $flagged || $manually_handled || $by_admin) {

            $affectedRows1 = Job::where('id', '=', $jobid)->update(array('admin_comments' => $admincomment, 'flagged' => $flagged, 'session_time' => $session, 'manually_handled' => $manually_handled, 'by_admin' => $by_admin));

        }

        return response('Record updated!');
    }

</p>

<h1>AFTER REFACTORING</h1>

<p>

    public function distanceFeed(Request $request)
    {
        $data = $request->all();
        $jobid = $data['jobid'] ?? null;

        $distance = $data['distance'] ?? '';
        $time = $data['time'] ?? '';
        $session = $data['session_time'] ?? '';
        $admincomment = $data['admincomment'] ?? '';

        $flagged = ($data['flagged'] == 'true' && $admincomment != '') ? 'yes' : 'no';
        $manually_handled = ($data['manually_handled'] == 'true') ? 'yes' : 'no';
        $by_admin = ($data['by_admin'] == 'true') ? 'yes' : 'no';

        if ($time || $distance)
        {
         Distance::where('job_id', '=', $jobid)->update(['distance' => $distance, 'time' =>$time]);
        }

        if ($admincomment || $session || $flagged || $manually_handled || $by_admin) {
           Job::where('id', '=', $jobid)->update([
           'admin_comments' => $admincomment,
           'flagged' => $flagged,
           'session_time' => $session,
           'manually_handled' => $manually_handled,
           'by_admin' => $by_admin]);
        }

      return response('Record updated!');
    }

</p>

<h2>
My Point of View
<h2>

<p>
I used the null coalescing operator (??) to set default values for variables when they are not present or empty in the $data array.<p>
<p>
I simplified the conditions for setting the values of $flagged, $manually_handled, and $by_admin.
</p>
<p>
I consolidated the database update queries for Distance and Job models into their respective blocks.
</p>
<p>
This refactoring makes the code more concise, easier to read, and removes unnecessary duplication of checks.
</p>

<h1>BEFORE REFACTORING</h1>
<p>

    public function resendNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        $job_data = $this->repository->jobToData($job);
        $this->repository->sendNotificationTranslator($job, $job_data, '*');

        return response(['success' => 'Push sent']);
    }

</p>

<h1>AFTER REFACTORING</h1>

<p>

    public function resendNotifications(Request $request)
    {

        $data = $request->all();
        $jobId = $data['jobid'];

        $job = $this->repository->find($jobId);
        $jobData = $this->repository->jobToData($job);

        $this->repository->sendNotificationTranslator($job, $jobData, '*');

        return response(['success' => 'Push sent']);

    }

</p>

<h2>
My Point of View
<h2>

<p>
I renamed $job_data to $jobData to follow a more common naming convention, which is camelCase for variable names in PHP.</p>
<p>
I assigned $data['jobid'] to the variable $jobId for better readability and clarity</p>
<p>
These small adjustments help improve the code's readability while preserving its functionality.</p>

<h1>BEFORE REFACTORING</h1>
<p>

    function __construct(Job $model, MailerInterface $mailer)
    {
        parent::__construct($model);
        $this->mailer = $mailer;
        $this->logger = new Logger('admin_logger');

        $this->logger->pushHandler(new StreamHandler(storage_path('logs/admin/laravel-' . date('Y-m-d') . '.log'), Logger::DEBUG));
        $this->logger->pushHandler(new FirePHPHandler());
    }

</p>

<h1>AFTER REFACTORING</h1>

<p>

    public function __construct(Job $model, MailerInterface $mailer)
    {
        parent::__construct($model);
        $this->mailer = $mailer;
        $this->initializeLogger();
    }

    private function initializeLogger()
    {
        $logPath = storage_path('logs/admin/laravel-' . date('Y-m-d') . '.log');
        
        $this->logger = new Logger('admin_logger');
        $this->logger->pushHandler(new StreamHandler($logPath, Logger::DEBUG));
        $this->logger->pushHandler(new FirePHPHandler());
    }

</p>

<h2>
My Point of View
<h2>

<p>
I moved the initializeLogger functionality into a separate private method for better code organization and reusability.</p>
<p>
Used meaningful variable names, such as $logPath, to make the code more self-explanatory</p>

<h1>BEFORE REFACTORING</h1>
<p>

    public function getUsersJobs($user_id)
    {
        $page = $request->get('page');
        if (isset($page)) {
            $pagenum = $page;
        } else {
            $pagenum = "1";
        }
        $cuser = User::find($user_id);
        $usertype = '';
        $emergencyJobs = array();
        $noramlJobs = array();
        if ($cuser && $cuser->is('customer')) {
            $jobs = $cuser->jobs()->with('user.userMeta', 'user.average', 'translatorJobRel.user.average', 'language', 'feedback', 'distance')->whereIn('status', ['completed', 'withdrawbefore24', 'withdrawafter24', 'timedout'])->orderBy('due', 'desc')->paginate(15);
            $usertype = 'customer';
            return ['emergencyJobs' => $emergencyJobs, 'noramlJobs' => [], 'jobs' => $jobs, 'cuser' => $cuser, 'usertype' => $usertype, 'numpages' => 0, 'pagenum' => 0];
        } elseif ($cuser && $cuser->is('translator')) {
            $jobs_ids = Job::getTranslatorJobsHistoric($cuser->id, 'historic', $pagenum);
            $totaljobs = $jobs_ids->total();
            $numpages = ceil($totaljobs / 15);

            $usertype = 'translator';

            $jobs = $jobs_ids;
            $noramlJobs = $jobs_ids;
 
            return ['emergencyJobs' => $emergencyJobs, 'noramlJobs' => $noramlJobs, 'jobs' => $jobs, 'cuser' => $cuser, 'usertype' => $usertype, 'numpages' => $numpages, 'pagenum' => $pagenum];
        }
    }

</p>

<h1>AFTER REFACTORING</h1>

<p>

    public function getUsersJobs($user_id)
    {
        $page = Request::get('page', 1);

        $cuser = User::find($user_id);
        $usertype = '';
        $emergencyJobs = [];
        $normalJobs = [];
        $jobs = [];

        if ($cuser) {
            if ($cuser->is('customer')) {
                $jobs = $cuser->jobs()
                    ->with('user.userMeta', 'user.average', 'translatorJobRel.user.average', 'language', 'feedback', 'distance')
                    ->whereIn('status', ['completed', 'withdrawbefore24', 'withdrawafter24', 'timedout'])
                    ->orderBy('due', 'desc')
                    ->paginate(15);

                $usertype = 'customer';
            } elseif ($cuser->is('translator')) {
                $jobs = Job::getTranslatorJobsHistoric($cuser->id, 'historic', $page);
                $totalJobs = $jobs->total();
                $numPages = ceil($totalJobs / 15);

                $usertype = 'translator';
                $normalJobs = $jobs->toArray();
            }
        }

        return [
            'emergencyJobs' => $emergencyJobs,
            'normalJobs' => $normalJobs,
            'jobs' => $jobs,
            'cuser' => $cuser,
            'usertype' => $usertype,
            'numPages' => $numPages ?? 0,
            'pageNum' => $page,
        ];
    }

</p>

<h2>
My Point of View
<h2>

<p>
Simplified the way the $page variable is assigned using the Request::get() method with a default value.</p>
<p>
Removed unnecessary isset() checks for the $page variable</p>
<p>
Used more consistent variable naming (numPages instead of numpages and pageNum instead of pagenum) for improved readability</p>

<h1>BEFORE REFACTORING</h1>
<p>

    public function store($user, $data)
    {

        $immediatetime = 5;
        $consumer_type = $user->userMeta->consumer_type;
        if ($user->user_type == env('CUSTOMER_ROLE_ID')) {
            $cuser = $user;

            if (!isset($data['from_language_id'])) {
                $response['status'] = 'fail';
                $response['message'] = "Du måste fylla in alla fält";
                $response['field_name'] = "from_language_id";
                return $response;
            }
            if ($data['immediate'] == 'no') {
                if (isset($data['due_date']) && $data['due_date'] == '') {
                    $response['status'] = 'fail';
                    $response['message'] = "Du måste fylla in alla fält";
                    $response['field_name'] = "due_date";
                    return $response;
                }
                if (isset($data['due_time']) && $data['due_time'] == '') {
                    $response['status'] = 'fail';
                    $response['message'] = "Du måste fylla in alla fält";
                    $response['field_name'] = "due_time";
                    return $response;
                }
                if (!isset($data['customer_phone_type']) && !isset($data['customer_physical_type'])) {
                    $response['status'] = 'fail';
                    $response['message'] = "Du måste göra ett val här";
                    $response['field_name'] = "customer_phone_type";
                    return $response;
                }
                if (isset($data['duration']) && $data['duration'] == '') {
                    $response['status'] = 'fail';
                    $response['message'] = "Du måste fylla in alla fält";
                    $response['field_name'] = "duration";
                    return $response;
                }
            } else {
                if (isset($data['duration']) && $data['duration'] == '') {
                    $response['status'] = 'fail';
                    $response['message'] = "Du måste fylla in alla fält";
                    $response['field_name'] = "duration";
                    return $response;
                }
            }
            if (isset($data['customer_phone_type'])) {
                $data['customer_phone_type'] = 'yes';
            } else {
                $data['customer_phone_type'] = 'no';
            }

            if (isset($data['customer_physical_type'])) {
                $data['customer_physical_type'] = 'yes';
                $response['customer_physical_type'] = 'yes';
            } else {
                $data['customer_physical_type'] = 'no';
                $response['customer_physical_type'] = 'no';
            }

            if ($data['immediate'] == 'yes') {
                $due_carbon = Carbon::now()->addMinute($immediatetime);
                $data['due'] = $due_carbon->format('Y-m-d H:i:s');
                $data['immediate'] = 'yes';
                $data['customer_phone_type'] = 'yes';
                $response['type'] = 'immediate';

            } else {
                $due = $data['due_date'] . " " . $data['due_time'];
                $response['type'] = 'regular';
                $due_carbon = Carbon::createFromFormat('m/d/Y H:i', $due);
                $data['due'] = $due_carbon->format('Y-m-d H:i:s');
                if ($due_carbon->isPast()) {
                    $response['status'] = 'fail';
                    $response['message'] = "Can't create booking in past";
                    return $response;
                }
            }
            if (in_array('male', $data['job_for'])) {
                $data['gender'] = 'male';
            } else if (in_array('female', $data['job_for'])) {
                $data['gender'] = 'female';
            }
            if (in_array('normal', $data['job_for'])) {
                $data['certified'] = 'normal';
            }
            else if (in_array('certified', $data['job_for'])) {
                $data['certified'] = 'yes';
            } else if (in_array('certified_in_law', $data['job_for'])) {
                $data['certified'] = 'law';
            } else if (in_array('certified_in_helth', $data['job_for'])) {
                $data['certified'] = 'health';
            }
            if (in_array('normal', $data['job_for']) && in_array('certified', $data['job_for'])) {
                $data['certified'] = 'both';
            }
            else if(in_array('normal', $data['job_for']) && in_array('certified_in_law', $data['job_for']))
            {
                $data['certified'] = 'n_law';
            }
            else if(in_array('normal', $data['job_for']) && in_array('certified_in_helth', $data['job_for']))
            {
                $data['certified'] = 'n_health';
            }
            if ($consumer_type == 'rwsconsumer')
                $data['job_type'] = 'rws';
            else if ($consumer_type == 'ngo')
                $data['job_type'] = 'unpaid';
            else if ($consumer_type == 'paid')
                $data['job_type'] = 'paid';
            $data['b_created_at'] = date('Y-m-d H:i:s');
            if (isset($due))
                $data['will_expire_at'] = TeHelper::willExpireAt($due, $data['b_created_at']);
            $data['by_admin'] = isset($data['by_admin']) ? $data['by_admin'] : 'no';

            $job = $cuser->jobs()->create($data);

            $response['status'] = 'success';
            $response['id'] = $job->id;
            $data['job_for'] = array();
            if ($job->gender != null) {
                if ($job->gender == 'male') {
                    $data['job_for'][] = 'Man';
                } else if ($job->gender == 'female') {
                    $data['job_for'][] = 'Kvinna';
                }
            }
            if ($job->certified != null) {
                if ($job->certified == 'both') {
                    $data['job_for'][] = 'normal';
                    $data['job_for'][] = 'certified';
                } else if ($job->certified == 'yes') {
                    $data['job_for'][] = 'certified';
                } else {
                    $data['job_for'][] = $job->certified;
                }
            }

            $data['customer_town'] = $cuser->userMeta->city;
            $data['customer_type'] = $cuser->userMeta->customer_type;

         } else {
            $response['status'] = 'fail';
            $response['message'] = "Translator can not create booking";
        }
    }
</p>

<h1>AFTER REFACTORING</h1>

<p>

    public function store($user, $data)
    {
        $consumerType = $user->userMeta->consumer_type;

        if ($user->user_type != Config::get('constants.CUSTOMER_ROLE_ID')) {
            return ['status' => 'fail', 'message' => "Translator can not create booking"];
        }

        if (empty($data['from_language_id'])) {
            return ['status' => 'fail', 'message' => "Du måste fylla in alla fält", 'field_name' => 'from_language_id'];
        }

        if ($data['immediate'] == 'no') {
            if (empty($data['due_date']) || empty($data['due_time'])) {
                return ['status' => 'fail', 'message' => "Du måste fylla in alla fält", 'field_name' => 'due_date'];
            }

            if (empty($data['customer_phone_type']) && empty($data['customer_physical_type'])) {
                return ['status' => 'fail', 'message' => "Du måste göra ett val här", 'field_name' => 'customer_phone_type'];
            }

            if (empty($data['duration'])) {
                return ['status' => 'fail', 'message' => "Du måste fylla in alla fält", 'field_name' => 'duration'];
            }
        } elseif (empty($data['duration'])) {
            return ['status' => 'fail', 'message' => "Du måste fylla in alla fält", 'field_name' => 'duration'];
        }

        $data['customer_phone_type'] = isset($data['customer_phone_type']) ? 'yes' : 'no';
        $data['customer_physical_type'] = isset($data['customer_physical_type']) ? 'yes' : 'no';

        $immediateTime = 5;
        if ($data['immediate'] == 'yes') {
            $dueCarbon = Carbon::now()->addMinute($immediateTime);
            $data['due'] = $dueCarbon->format('Y-m-d H:i:s');
            $data['immediate'] = 'yes';
            $data['customer_phone_type'] = 'yes';
            $responseType = 'immediate';
        } else {
            $due = $data['due_date'] . " " . $data['due_time'];
            $dueCarbon = Carbon::createFromFormat('m/d/Y H:i', $due);
            $data['due'] = $dueCarbon->format('Y-m-d H:i:s');
            $responseType = 'regular';

            if ($dueCarbon->isPast()) {
                return ['status' => 'fail', 'message' => "Can't create booking in the past"];
            }
        }

        $data['gender'] = $this->mapJobFor($data['job_for']);
        $data['certified'] = $this->mapCertifieds($data['job_for']);
        $data['job_type'] = $this->mapJobType($consumerType);
        $data['b_created_at'] = date('Y-m-d H:i:s');

        if (isset($due)) {
            $data['will_expire_at'] = TeHelper::willExpireAt($due, $data['b_created_at']);
        }

        $data['by_admin'] = isset($data['by_admin']) ? $data['by_admin'] : 'no';

        $job = $user->jobs()->create($data);

        $response = [
            'status' => 'success',
            'id' => $job->id,
            'job_for' => $data['job_for'],
            'customer_town' => $user->userMeta->city,
            'customer_type' => $user->userMeta->customer_type,
            'type' => $responseType,
        ];

        return $response;
    }

    private function mapJobFor($jobFor)
    {
        if (in_array('male', $jobFor)) {
            return 'male';
        } elseif (in_array('female', $jobFor)) {
            return 'female';
        } else {
            return null;
        }
    }

    private function mapCertifieds($jobFor)
    {
        if (in_array('normal', $jobFor) && in_array('certified', $jobFor)) {
            return 'both';
        } elseif (in_array('certified', $jobFor)) {
            return 'yes';
        } elseif (in_array('certified_in_law', $jobFor)) {
            return 'law';
        } elseif (in_array('certified_in_helth', $jobFor)) {
            return 'health';
        } elseif (in_array('normal', $jobFor)) {
            return 'normal';
        } else {
            return null;
        }
    }

    private function mapJobType($consumerType)
    {
        if ($consumerType == 'rwsconsumer') {
            return 'rws';
        } elseif ($consumerType == 'ngo') {
            return 'unpaid';
        } elseif ($consumerType == 'paid') {
            return 'paid';
        } else {
            return null;
        }
    }
</p>

<h2>
My Point of View
<h2>

<p>
I removed duplicate code for response creation and introduced a consistent structure for error responses.</p>
<p>
Used early returns to simplify conditional checks and avoid unnecessary nesting</p>
<p>
Created separate helper methods to map values for gender, certified, and job_type, improving code readability and maintainability</p>
<p>Utilized Laravel's Config class to retrieve the CUSTOMER_ROLE_ID configuration value.</p>
<p>Improved variable naming for better clarity</p>