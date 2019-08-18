<?php

$login = array(
    array(
        'field' => 'email',
        'rules' => 'required|callback__email_exists',
        'errors' => array(
            'required' => 'Please enter your email or phone.',
        )
    ),
    array(
        'field' => 'password',
        'rules' => 'required',
        'errors' => array(
            'required' => 'Please enter password.',
        )
    ),
);

$config['login/index'] = $login;

$addUser = array(
    array(
        'field' => 'firstname',
        'rules' => 'required',
        'errors' => array(
            'required' => 'Please enter firstname.',
        ) 
    ),
    // array(
    //     'field' => 'lastname',
    //     'rules' => 'required',
    //     'errors' => array(
    //         'required' => 'Please enter lastname.',
    //     ) 
    // ),
    array(
        'field' => 'email',
        'rules' => 'valid_email|callback__email_exists',
        'errors' => array(
            'valid_email' => 'Please enter valid email.',
        ) 
    ),
    // array(
    //     'field' => 'dob',
    //     'rules' => 'required',
    //     'errors' => array(
    //         'required' => 'Please enter date of birth.',
    //     ) 
    // ),
    array(
        'field' => 'phone',
        'rules' => 'required|callback__phone_exists',
        'errors' => array(
            'required' => 'Please enter phone number.',
            
        ) 
    ),
    array(
        'field' => 'gender',
        'rules' => 'required',
        'errors' => array(
            'required' => 'Please select gender.',
        ) 
    ),
    // array(
    //     'field' => 'state',
    //     'rules' => 'required',
    //     'errors' => array(
    //         'required' => 'Please select state.',
    //     ) 
    // ),
    // array(
    //     'field' => 'district',
    //     'rules' => 'required',
    //     'errors' => array(
    //         'required' => 'Please select district.',
    //     ) 
    // ),
    // array(
    //     'field' => 'mandal',
    //     'rules' => 'required',
    //     'errors' => array(
    //         'required' => 'Please select mandal.',
    //     ) 
    // ),
    // array(
    //     'field' => 'village',
    //     'rules' => 'required',
    //     'errors' => array(
    //         'required' => 'Please select village.',
    //     ) 
    // ),
    // array(
    //     'field' => 'category',
    //     'rules' => 'required',
    //     'errors' => array(
    //         'required' => 'Please select category.',
    //     ) 
    // ),
	// array(
    //     'field' => 'caste',
    //     'rules' => 'required',
    //     'errors' => array(
    //         'required' => 'Please select caste.',
    //     ) 
    // ), 
    // array(
    //     'field' => 'religion',
    //     'rules' => 'required',
    //     'errors' => array(
    //         'required' => 'Please select religion.',
    //     ) 
    // ),
    array(
        'field' => 'photo',
        'rules' => 'callback__file_check',
    ),
    // array(
    //     'field' => 'voterId',
    //     'rules' => 'required',
    //     'errors' => array(
    //         'required' => 'Please enter voter id.',
    //     ) 
    // ),
    // array(
    //     'field' => 'qualification[]',
    //     'rules' => 'required',
    //     'errors' => array(
    //         'required' => 'Please select at least one highest qualification.',
    //     ) 
    // ),
    // array(
    //     'field' => 'hno',
    //     'rules' => 'required',
    //     'errors' => array(
    //         'required' => 'Please enter house number.',
    //     ) 
    // ),
    // array(
    //     'field' => 'street',
    //     'rules' => 'required',
    //     'errors' => array(
    //         'required' => 'Please enter street.',
    //     ) 
    // ),
    // array(
    //     'field' => 'password',
    //     'rules' => 'required|min_length[8]|max_length[16]',
    //     'errors' => array(
    //         'required' => 'Please enter house number.',
    //         'min_length' => 'Password should be between 8 to 16 characters long',
    //         'max_length' => 'Password should be between 8 to 16 characters long',
    //     ) 
    // ),
    // array(
    //     'field' => 'confirmpassword',
    //     'rules' => 'matches[password]',
    //     'errors' => array(
    //         'matches' => 'Passwords do not match',
    //     ) 
    // ),
);

$config['SeniorManager/adduser'] = $addUser;
$config['manager/adduser'] = $addUser;
$config['user/adduser'] = $addUser;
$config['admin/adduser'] = $addUser;

$editUser = array(
    array(
        'field' => 'firstname',
        'rules' => 'required',
        'errors' => array(
            'required' => 'Please enter firstname.',
        ) 
    ),
    // array(
    //     'field' => 'lastname',
    //     'rules' => 'required',
    //     'errors' => array(
    //         'required' => 'Please enter lastname.',
    //     ) 
    // ),
);

$config['admin/edit'] = $editUser;

$pollingAgent = array(
    array(
        'field' => 'firstname',
        'rules' => 'required',
        'errors' => array(
            'required' => 'Please enter firstname.',
        ) 
    ),
    array(
        'field' => 'pollingno',
        'rules' => 'required',
        'errors' => array(
            'required' => 'Please select polling station',
        ) 
    ),
	array(
        'field' => 'age',
        'rules' => 'numeric',
        'errors' => array(
            'numeric' => 'Please enter numeric value',
        ) 
    ),
);
$config['admin/polingagent'] = $pollingAgent;

$contestant = array(
    array(
        'field' => 'party',
        'rules' => 'required',
        'errors' => array(
            'required' => 'Please select party.',
        ) 
    ),
);
$config['admin/contestant'] = $contestant;

$config['manager/villageanalytics'] = array(
    array(
        'field' => 'village',
        'rules' => 'required',
        'errors' => array(
            'required' => 'Please select village.',
        ) 
    ),
);

$config['manager/location'] = array(
    array(
        'field' => 'constituency',
        'rules' => 'required',
        'errors' => array(
            'required' => 'Please select constituency.',
        ) 
    ),
);

$config['manager/assignrole'] = array(
    array(
        'field' => 'user',
        'rules' => 'required',
        'errors' => array(
            'required' => 'Please select user.',
        ) 
    ),
    array(
        'field' => 'user-role',
        'rules' => 'required',
        'errors' => array(
            'required' => 'Please select user role.',
        ) 
    ),
    // array(
    //     'field' => 'location',
    //     // 'rules' => 'required|callback__village_allocate',
    //     'rules' => 'required',
    //     'errors' => array(
    //         'required' => 'Please select location to assign.',
    //     ) 
    // )
);

$config['SeniorManager/assignrole'] = array(
    array(
        'field' => 'user',
        'rules' => 'required',
        'errors' => array(
            'required' => 'Please select user.',
        ) 
    ),
    array(
        'field' => 'user-role',
        'rules' => 'required',
        'errors' => array(
            'required' => 'Please select user role.',
        ) 
    ),
    array(
        'field' => 'location',
        'rules' => 'required|callback__mandal_allocate',
        'errors' => array(
            'required' => 'Please select location to assign.',
        ) 
    )
);

$config['administrator/assignrole'] = array(
    array(
        'field' => 'user',
        'rules' => 'required',
        'errors' => array(
            'required' => 'Please select user.',
        ) 
    ),
    array(
        'field' => 'user-role',
        'rules' => 'required',
        'errors' => array(
            'required' => 'Please select user role.',
        ) 
    ),
    // array(
    //     'field' => 'district',
    //     'rules' => 'required',
    //     'errors' => array(
    //         'required' => 'Please select district.',
    //     ) 
    // ) 
);

$config['SeniorManager/assignmandal'] = array(
    array(
        'field' => 'user',
        'rules' => 'required',
        'errors' => array(
            'required' => 'Please select user.',
        ) 
    ),
    array(
        'field' => 'user-mandal',
        'rules' => 'required',
        'errors' => array(
            'required' => 'Please select mandal.',
        ) 
    ),
);
$tasks = array(
    array(
        'field' => 'taskname',
        'rules' => 'required',
        'errors' => array(
            'required' => 'Please Enter Title.',
        )
    ),
    array(
        'field' => 'receiver',
        'rules' => 'required',
        'errors' => array(
            'required' => 'Please select Member Group.',
        )
    ),
	array(
        'field' => 'datefrom',
        'rules' => 'required',
        'errors' => array(
            'required' => 'Please select Date From.',
        )
    ),
	array(
        'field' => 'dateto',
        'rules' => 'required',
        'errors' => array(
            'required' => 'Please select Date To.',
        )
    ),
);

$config['SeniorManager/tasks'] = $tasks;

$config['admin/sharecontact'] = array(
    array(
        'field' => 'mandal',
        'rules' => 'required',
        'errors' => array(
            'required' => 'Please Select Mandal.',
        )
    ),
    array(
        'field' => 'rolefrom',
        'rules' => 'required',
        'errors' => array(
            'required' => 'Please Select Contact to Share.',
        )
    ),
    array(
        'field' => 'roleto',
        'rules' => 'required',
        'errors' => array(
            'required' => 'Please Select Users to share.',
        )
    ),
);