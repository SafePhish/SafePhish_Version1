<?php

namespace App\Http\Controllers;

use App\Models\Default_Settings;
use App\Models\Project;
use App\Template;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController as Auth;
use App\Models\User;
use League\Flysystem\Exception;

class GUIController extends Controller
{
    public static function generateLogin() {
        if(!Auth::check()) {
            return view("auth.loginTest");
        }
        return redirect()->to('/');
    }

    public static function generateRegister() {
        if(!Auth::check()) {
            return view("auth.register");
        }
        return redirect()->to('/');
    }

    /**
     * generatePhishingEmailForm
     * Generates the Send Phishing Email Request Form in the GUI.
     *
     * @return  \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public static function generatePhishingEmailForm() {
        if(Auth::check()) {
            $user = \Session::get('authUser');
            $settings = Default_Settings::where('DFT_UserId',$user->USR_Id)->first();
            $projects = Project::all();
            $templates = self::returnAllTemplatesByDirectory('phishing');

            $variables = array('settings'=>$settings,'projects'=>$projects,'templates'=>$templates);
            return view('forms.phishingEmail')->with($variables);
        } else {
            \Session::put('intended',$_SERVER['REQUEST_URI']);
            return redirect()->route('login');
        }
    }

    /**
     * updateDefaultEmailSettings
     * Post function for updating Default Email Settings, which are used to autofill the Send Email Request Form
     *
     * @param   Request         $request            Settings sent from form
     */
    public static function updateDefaultEmailSettings(Request $request) {
        $user = User::where('USR_Username',$request->input('usernameText'))->first();
        $company = $request->input('companyText');
        $host = $request->input('mailServerText');
        $port = $request->input('mailPortText');

        $settings = Default_Settings::firstOrNew(['DFT_UserId'=>$user->USR_Id]);
        $settings->DFT_MailServer = $host;
        $settings->DFT_MailPort = $port;
        $settings->DFT_CompanyName = $company;
        $settings->DFT_Username = $user->USR_Username;
        $settings->save();
    }

    /**
     * generateDefaultEmailSettingsForm
     * Generates the Settings form based on input from the database.
     *
     * @return  \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public static function generateDefaultEmailSettingsForm() {
        if(Auth::check()) {
            $user = \Session::get('authUser');
            $settings = Default_Settings::where('DFT_UserId',$user->USR_Id)->first();
            if(count($settings)) {
                $dft_host = $settings->DFT_MailServer;
                $dft_port = $settings->DFT_MailPort;
                $dft_user = $settings->DFT_Username;
                $dft_company = $settings->DFT_CompanyName;
            } else {
                $dft_host = '';
                $dft_port = '';
                $dft_company = '';
                $dft_user = '';
            }
            $varToPass = array('dft_host'=>$dft_host,'dft_port'=>$dft_port,'dft_user'=>$dft_user,'dft_company'=>$dft_company);
            return view('forms.defaultEmailSettings')->with($varToPass);
        } else {
            \Session::put('intended',route('defaultSettings')); //define route defaultSettings
            return redirect()->route('login');
        }
    }

    /**
     * createNewPhishTemplate
     * Creates new phishing template and writes it to the file structure.
     *
     * @param   Request         $request        Template name and content sent by user to create new template
     */
    public static function createNewPhishTemplate(Request $request) {
        $name = $request->input('templateName');
        $content = $request->input('templateContent');
        Template::createPhish($content,$name);
    }

    /**
     * createNewEduTemplate
     * Creates new educational template and writes it to the file structure.
     *
     * @param   Request         $request        Template name and content sent by user to create new template
     */
    public static function createNewEduTemplate(Request $request) {
        $name = $request->input('templateName');
        $content = $request->input('templateContent');
        Template::createEdu($content,$name);
    }

    /**
     * createNewProject
     * Creates new project and inserts into database.
     *
     * @param   Request         $request        Data sent by user to instantiate new project
     */
    public static function createNewProject(Request $request) {
        $user = \Session::get('authUser');
        Project::create(
            ['PRJ_Name'=>$request->input('projectName'),
            'PRJ_Description'=>$request->input('projectDescription'),
            'PRJ_ComplexityType'=>$request->input('projectComplexityType'),
            'PRJ_TargetType'=>$request->input('projectTargetType'),
            'PRJ_Assignee'=>$user->USR_Id,
            'PRJ_Status'=>'active']
        );
    }

    /**
     * returnAllTemplatesByDirectory
     * Helper function that queries template names from specific directory in file structure.
     *
     * @param   string      $directory          Directory name
     * @return  array
     */
    private static function returnAllTemplatesByDirectory($directory) {
        $files = [];
        $fileNames = [];
        $filesInFolder = \File::files("../resources/views/emails/$directory");
        foreach($filesInFolder as $path) {
            $files[] = pathinfo($path);
        }
        for($i = 0; $i < sizeof($files); $i++) {
            $fileNames[$i] = $files[$i]['filename'];
            $fileNames[$i] = substr($fileNames[$i],0,-6);
        }
        return $fileNames;
    }

    /**
     * phishHTMLReturner
     * Takes phishing template name as input and returns content of template to
     * be used as a preview in the GUI.
     *
     * @param   string      $id             Template Name
     * @return  string                      Template Content
     */
    public static function phishHTMLReturner($id) {
        try {
            $path = "../resources/views/emails/phishing/$id.blade.php";
            $contents = \File::get($path);
        }
        catch (Exception $e) {
            //log exception
            $contents = "Preview Unavailable";
        }
        return $contents;
    }
}
