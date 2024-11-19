<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Exception;
use Log;
use App\Models\ZohoAccessToken;

class TaskController extends Controller
{

    public $clientid, $client_secret, $grant_type;

    public $zohocurrentToken;
    public $zohorefereshToken;

    public function __construct()
    {
        $this->clientid =  config('services.zoho.client_id');
        $this->client_secret =  config('services.zoho.client_secret');
        $this->grant_type =  config('services.zoho.grant_type');
        $zohodata = ZohoAccessToken::take(1)->first();
        $this->zohocurrentToken = $zohodata->current_token;
        $this->zohorefereshToken = $zohodata->refresh_token;
    }

    // Fetch all tasks
    public function index()
    {
        $tasks = Task::all();  // Retrieve all tasks from the database
        return response()->json($tasks);  // Return tasks as a JSON response
    }

    // Fetch a single task by ID
    public function show($id)
    {
        $task = Task::find($id);  // Find task by ID
        if ($task) {
            return response()->json($task);  // Return task as JSON
        }
        return response()->json(['message' => 'Task not found'], 404);
    }

    // Store a new task
    public function store(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        // Create a new task
        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return response()->json($task, 201);  // Return the created task with a 201 status
    }

    // Update an existing task
    public function update(Request $request, $id)
    {
        // Find the task by ID
        $task = Task::find($id);
        if ($task) {
            // Validate and update the task
            $task->update([
                'title' => $request->title,
                'description' => $request->description,
            ]);
            return response()->json($task);  // Return the updated task as JSON
        }
        return response()->json(['message' => 'Task not found'], 404);
    }

    // Delete a task
    public function destroy($id)
    {
        // Find the task by ID
        $task = Task::find($id);
        if ($task) {
            $task->delete();  // Delete the task
            return response()->json(['message' => 'Task deleted successfully']);
        }
        return response()->json(['message' => 'Task not found'], 404);
    }


    public function timesheetData(Request $request)
    {


        $timsheetParam = [
            'user' => 'kiran.muli@vinzglobal.com',
            'approvalStatus' => 'approved',
            'employeeStatus' => 'users',
            'dateFormat' => 'dd-MMM-yyyy',
            'fromDate' => '07-Apr-2024',
            'toDate' => '13-Apr-2024',
            'sIndex' => '0',
            'limit' => '1'

        ];


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://people.zoho.in/people/api/timetracker/gettimesheet?user=' . $timsheetParam['user'] . '&approvalStatus=' . $timsheetParam['approvalStatus'] . '&employeeStatus=' . $timsheetParam['employeeStatus'] . '&dateFormat=' . $timsheetParam['dateFormat'] . '&fromDate=' . $timsheetParam['fromDate'] . '&toDate=' . $timsheetParam['toDate'] . '&sIndex=' . $timsheetParam['sIndex'] . '&limit=' . $timsheetParam['limit'] . '',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Zoho-oauthtoken ' . $this->zohocurrentToken . '',
            ),
        ));

        $response = curl_exec($curl);

        // Decode the JSON into a PHP array
        $data = json_decode($response, true);

        // Prepare the header row (keys from the first item)
        $headers = [
            'owner',
            'formId',
            'nonbillableHours',
            'employeeName',
            'erecno',
            'totalHours',
            'toDate',
            'description',
            'employeeEmail',
            'approvedBillableHours',
            'employeeId',
            'billableHours',
            'recordId',
            'listId',
            'fromDate',
            'totalAmount',
            'approvedNonBillableHours',
            'approvedRatePerHour',
            'approvedTotalAmount',
            'ratePerHour',
            'timesheetName',
            'currency',
            'approvedTotalHours',
            'status'
        ];

        // Open a file for writing
        $file = fopen('timesheet_data.csv', 'w');

        // Write the headers to the CSV file
        fputcsv($file, $headers);

        // Loop through the results and write each item to the CSV
        foreach ($data['response']['result'] as $item) {
            $row = [];
            foreach ($headers as $header) {
                // Add the corresponding value to the row
                $row[] = isset($item[$header]) ? $item[$header] : '';
            }
            // Write the row to the CSV
            fputcsv($file, $row);
        }

        // Close the file
        fclose($file);


        // curl_close($curl);
        return json_decode($response);
    }






    public function timesheetDetailsData(Request $request)
    {


        $timsheetParam = [
            'timesheetId' => '67398000004569478',
            'dateFormat' => 'dd-MMM-yyyy',

        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://people.zoho.in/people/api/timetracker/gettimesheetdetails?timesheetId=' . $timsheetParam['timesheetId'] . '&dateFormat=' . $timsheetParam['dateFormat'] . '',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Zoho-oauthtoken ' . $this->zohocurrentToken . '',
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);


        // Decode the JSON response into a PHP array
        $data = json_decode($response, true);

        // Open or create a CSV file
        $filename = $timsheetParam['timesheetId'] . '_timesheet_full_data.csv';
        $file = fopen($filename, 'w');

        // Check if the file was opened successfully
        if ($file === false) {
            die('Error opening the file');
        }

        // Write the CSV headers (all fields you want to include)
        fputcsv($file, [
            'Record No',
            'Client Name',
            'Employee Name',
            'Employee Email',
            'Job Name',
            'Description',
            'Work Date',
            'Approval Status',
            'Hours',
            'Billing Status',
            'Task Name',
            'Project Name',
            'Approval Submission Time',
            'Current Approval Status',
            'Can Approve All Levels',
            'Is Record Approver',
            'Pending Approval Level',
            'Is Owner',
            'Is Timelog Pushed to QBO',
            'Is Timelog Edit Allowed',
            'Job Billable Status',
            'Employee First Name',
            'Employee Last Name',
            'Work Date DB',
            'Job Is Completed',
            'Total Time',
            'Job Id',
            'Job Color',
            'Leave Details',
            'Paid Leave Hours',
            'Holiday Hours',
            'Max Hours per Day',
            'Min Hours Restrict',
            'From Date',
            'To Date',
            'Rate per Hour',
            'Currency'
        ]);

        // Loop through the timesheet entries (tsArr) and write them to CSV
        foreach ($data['response']['result']['tsArr'] as $timesheet) {
            $recordNo = $timesheet['erecno'];
            $clientName = $timesheet['clientName'];
            $employeeName = $timesheet['employeeFirstName'] . ' ' . $timesheet['employeeLastName'];
            $employeeEmail = $timesheet['employeeMailId'];
            $jobName = $timesheet['jobName'];
            $description = $timesheet['description'];
            $workDate = $timesheet['workDate'];
            $approvalStatus = $timesheet['approvalStatus'];
            $hours = $timesheet['hours'];
            $billingStatus = $timesheet['billingStatus'];
            $taskName = $timesheet['taskName'];
            $projectName = $timesheet['projectName'];
            $approvalSubmissionTime = $data['response']['result']['approvalDetails']['approvalSubmissionTime'];

            // Extract additional fields from approvalDetails and other places
            $currentApprovalStatus = $data['response']['result']['approvalDetails']['currentApprovalStatus'];
            $canApprAllLevels = $data['response']['result']['approvalDetails']['canApprAllLevels'] ? 'Yes' : 'No';
            $isRecordApprover = $data['response']['result']['approvalDetails']['isRecordApprover'] ? 'Yes' : 'No';
            $pendingApprovalLevel = $data['response']['result']['approvalDetails']['pendingApprovalLevel'];
            $isOwner = $data['response']['result']['approvalDetails']['isOwner'] ? 'Yes' : 'No';
            $isTimelogPushedToQBO = $timesheet['isTimelogPushedToQBO'] ? 'Yes' : 'No';
            $isTimelogEditAllowed = $data['response']['result']['details']['isTimelogEditAllowed'] ? 'Yes' : 'No';
            $jobBillableStatus = $timesheet['jobBillableStatus'];
            $employeeFirstName = $timesheet['employeeFirstName'];
            $employeeLastName = $timesheet['employeeLastName'];
            $workDateDB = $timesheet['db_workDate'];
            $jobIsCompleted = $timesheet['jobIsCompleted'];
            $totalTime = $timesheet['totaltime'];
            $jobId = $timesheet['jobId'];
            $jobColor = $timesheet['jobColor'];

            // Handle leave details
            $leaveDetails = '';
            if (!empty($data['response']['result']['leaveData']['leaveJson']['leaveDetails'])) {
                foreach ($data['response']['result']['leaveData']['leaveJson']['leaveDetails'] as $leave) {
                    $leaveDetails .= $leave['name'] . ' (' . $leave['workdate'] . '), ';
                }
            }

            $paidLeaveHours = $data['response']['result']['leaveData']['leaveJson']['paidLeaveHours'];
            $holidayHours = $data['response']['result']['leaveData']['leaveJson']['holidayHours'];

            // Other details from 'details' array
            $maxHrsDay = $data['response']['result']['details']['maxHrsDay'];
            $minHrsRestrict = $data['response']['result']['details']['isMinRestrict'] ? 'Yes' : 'No';
            $fromDate = $data['response']['result']['details']['fromDate'];
            $toDate = $data['response']['result']['details']['toDate'];
            $ratePerHour = $data['response']['result']['details']['ratePerHour'];
            $currency = $data['response']['result']['details']['currency'];

            // Write row to CSV
            fputcsv($file, [
                $recordNo,
                $clientName,
                $employeeName,
                $employeeEmail,
                $jobName,
                $description,
                $workDate,
                $approvalStatus,
                $hours,
                $billingStatus,
                $taskName,
                $projectName,
                $approvalSubmissionTime,
                $currentApprovalStatus,
                $canApprAllLevels,
                $isRecordApprover,
                $pendingApprovalLevel,
                $isOwner,
                $isTimelogPushedToQBO,
                $isTimelogEditAllowed,
                $jobBillableStatus,
                $employeeFirstName,
                $employeeLastName,
                $workDateDB,
                $jobIsCompleted,
                $totalTime,
                $jobId,
                $jobColor,
                $leaveDetails,
                $paidLeaveHours,
                $holidayHours,
                $maxHrsDay,
                $minHrsRestrict,
                $fromDate,
                $toDate,
                $ratePerHour,
                $currency
            ]);
        }

        // Close the file after writing
        fclose($file);

        echo "Data has been written to '$filename' successfully.\n";
    }



    public function refereshzohotoken()
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://accounts.zoho.in/oauth/v2/token?refresh_token=' . $this->zohorefereshToken . '&client_id=' . $this->clientid . '&client_secret=' . $this->client_secret . '&grant_type=' . $this->grant_type . '');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, []);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            print curl_error($ch);
        }
        curl_close($ch);
        Log::Info(["access_token_Data" => $response]);

        $response = json_decode($response);
        Log::Info(["access_token_Data" => $response]);

        if (!empty($response->access_token)) {

            $date = date('m/d/Y h:i:s a', time());
            ZohoAccessToken::where('id', 1)
                ->update([
                    'current_token' => $response->access_token,
                    'token_refereshed_at' => $date
                ]);

            Log::Info("Zoho Referesh token cron end.");
            return $response;
        }
    }
}
