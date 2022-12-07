<?php

return [

    'system_user_id' => '1',
    'per_page' => '10',

    'max_data_allowed' => '5',

    'image' => [
        'dir_path' => '/storage/',
        'default_img' => '/storage/images/default.png',
        'height' => '100',
        'width' => '100',
    ],

    'status' => ['0', '1', '2'],
    'status_text' => ['Inactive', 'Active', 'Block'],

    'permission' => [
        'has_permission' => '1',
        'has_not_permission' => '0',
        'permission_assign_success' => 'Permission assign successfully.',
        'permission_assign_failure' => 'Permission assign failed.',
        'permission_revert_success' => 'Permission reverted successfully.',
        'permission_revert_failure' => 'Permission revert failed.',
        'is_permission' => ['0', '1'],
        'is_permission_text' => ['No', 'Yes']
    ],

    'validation_codes' => [
        'unauthorized' => "401",
        'forbidden' => "403",
        'not_found' => "404",
        'content_not_found' => "206",
        'not_verified' => "405",
        'unprocessable_entity' => "422",
        'created' => "201",
        'ok' => "200",
    ],

    'users' => [
        'gender' => ['0', '1'],
        'gender_text' => ['Female', 'Male'],

        'user_type' => ['0', '1', '2', '3', '4'],
        'user_type_text' => [
            'Super Admin',
            'Admin',
            'Sub Admin',
            'Lead Manager',
            'Sales Associates'
        ],

        'number_of_calls' => ['20', '50'],
    ],

    'lead_manager' => [
        'type' => ['0', '1'],
        'type_text' => ['Direct', 'Sub Admin Assigned'],
    ],

    'sales_associate' => [
        'type' => ['0', '1', '2'],
        'type_text' => ['Direct', 'Sub Admin Assigned', 'Lead Manager Assigned'],
    ],

    'call_detail' => [
        'is_appointed' => ['0', '1'],
        'is_appointed_text' => ['No', 'Yes'],

        'reason' => ['0', '1', '2', '3', '4'],
        'reason_text' => ['Master Franchise', 'Franchise', 'Lead Manager', 'Consultant', 'Subscriber'],
    ],
  
    'time_log' => [
        'is_early_out' => ['0', '1'],
        'is_early_out_text' => ['No', 'Yes'],

        'flag' => ['0', '1', '2'],
        'flag_text' => ['Red', 'Yellow', 'Green'],
    ],

    'messages' => [
        'success' => [
            'login' => 'You are logged in successfully.',
            'logout' => 'You are logged out successfully.',
            'email_varify' => 'Email is varified successfully.',
            'mobile_varify' => 'Mobile is varified successfully.',
            'email_otp_resend' => 'Otp resend to your email successfully.',
            'mobile_otp_resend' => 'Otp resend to yout mobile successfully.',
            'password_changed' => 'Password is changes successfully.',
            'forgot_password' => 'We have emailed your password reset link!',
            'password_reset' => 'Your password has been reset!',
            'listed' => 'Record(s) has been listed successfully.',
            'showed' => 'Record has been shown successfully.',
            'stored' => 'Record has been stored successfully.',
            'deleted' => 'Record(s) has been deleted successfully.',
            'updated' => 'Record has been updated successfully.',
            'saved' => 'Record has been saved successfully.',
            'imported' => 'Excel sheet has been imported successfully',
        ],

        'errors' => [
            'max_one_allowed' => 'Only one record can be insert.',
            'not_found' => 'Records not found.',
            'already_exists' => 'Record(s) is already exists.',
            'something_wrong' => 'Something went wrong.',
            'user_has_not_permission' => 'You don\'t have permission to access.',
            'role_already_has_permission' => "Given permission already exists for this role.",
            'token_not_found' => 'Authorization Token not found',
            'invalid_token' => 'This password reset token is invalid.',
            'old_pwd_invalid' => 'The Old password is incorrect.',
            'invalid_old_password' => 'Invalid old password.',
            'invalid' => 'User is Invalid.',
            'account_not_verified' => 'Account is not varified.',
            'invalid_password' => 'Password is invalid.',
            'email_not_varified' => 'User has not varified email.',
            'mobile_not_varified' => 'User has not varified mobile.',
            'unauthorized_access' => 'Unauthorized access.',
            'email_already_varified' => 'Email already varified.',
            'mobile_already_varified' => 'Mobile already varified.',
            'system_user_delete' => 'The system user can\'t be deleted',
            'no_keyword_to_search' => 'Enter keyword to search.',
            'content_not_found' => 'No content available.',
            'not_found' => 'No route / path / url available.',
            'user_has_other_role' => 'This user already has other role.',
            'not_subscribe' => 'You have not subscribe to any plan',
            'unique_email' => 'Email ID must be unique',
            'unique_mobile' => 'Mobile no. must be unique',
            'email_already_taken' => 'Email ID is already taken for this follow up number',
            'mobile_already_taken' => 'Mobile number is already taken for this follow up number',
            'in_time_already_added' => 'You have already added in time for today',
            'out_time_already_added' => 'You have already added out time for today',
            'invalid_file_format' => 'Your file format is invalid',
            'wrong_data' => 'Inserted data is invalid'
        ]
    ]
];
