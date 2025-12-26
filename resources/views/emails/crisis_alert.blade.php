<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .alert-box { border: 2px solid #dc2626; background-color: #fee2e2; padding: 15px; border-radius: 5px; color: #991b1b; }
        .btn { display: inline-block; background-color: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
<h2>Critical Alert Notification</h2>

<p>Dear Dr. {{ $alert->psychologist->name }},</p>

<p>One of your patients, <strong>{{ $patient->name }}</strong>, has just reported a critical status update.</p>

<div class="alert-box">
    <h3>Alert Details:</h3>
    <ul>
        <li><strong>Severity:</strong> <span style="color:red; font-weight:bold;">CRITICAL</span></li>
        <li><strong>Reason:</strong> {{ $alert->reason }}</li>
        <li><strong>Time Reported:</strong> {{ $alert->created_at->format('d M Y, H:i') }}</li>
    </ul>
</div>

<p>Please review their file and consider contacting them immediately.</p>

<a href="{{ url('/psychologist/patient/' . $patient->id) }}" class="btn">
    View Patient File
</a>
</body>
</html>
