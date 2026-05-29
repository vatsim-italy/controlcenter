@extends('layouts.app')

@section('title', 'Privacy Policy')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-body p-4 p-lg-5">
                    <p class="text-muted mb-4">Last updated: 29 May 2026</p>

                    <p>
                        Control Center is a training, exam, booking, and feedback management system for VATSIM regions.
                        It processes personal data only to operate the service, administer training and bookings,
                        send operational notifications, and support account maintenance and security.
                    </p>

                    <p>
                        This policy describes the data handled by this Control Center deployment and how that data is used.
                    </p>

                    <h4 class="mt-4">Data We Collect</h4>
                    <p>We collect and store the data needed to run the platform, including:</p>
                    <ul>
                        <li>Account and profile data from VATSIM authentication, such as CID, name, email address, rating details, region, division, and subdivision.</li>
                        <li>Authentication and session data, including remember tokens and VATSIM access or refresh tokens when they are stored for login integration.</li>
                        <li>Training and exam data, such as training requests, motivation text, status changes, mentor assignments, exam results, endorsements, comments, and related timestamps.</li>
                        <li>Feedback data, including feedback text, the submitting user, the referenced user or position, visibility and follow-up choices, and reply status.</li>
                        <li>Activity and audit data, including administrative activity logs, IP addresses, user agents, and timestamps.</li>
                        <li>Booking, task, vote, notification, and file metadata used to operate the platform.</li>
                        <li>User preferences such as notification settings and the optional workmail address and its expiry date.</li>
                        <li>Technical and diagnostic data such as error reports, telemetry metadata, and service configuration values needed to keep the instance running.</li>
                    </ul>

                    <h4 class="mt-4">How We Use Data</h4>
                    <p>We use personal data to:</p>
                    <ul>
                        <li>Authenticate users through VATSIM.</li>
                        <li>Manage training, exams, mentoring, endorsements, bookings, and feedback.</li>
                        <li>Send system notifications, emails, and Discord messages when those features are enabled.</li>
                        <li>Track account state, activity, and operational history for staff administration.</li>
                        <li>Detect abuse, investigate incidents, and maintain security.</li>
                        <li>Support account deletion, pseudonymization, and other data subject requests.</li>
                    </ul>

                    <h4 class="mt-4">Data Sources and Sharing</h4>
                    <p>
                        Control Center is designed to work with VATSIM authentication and VATSIM Core API data.
                        That means profile data may be imported or refreshed from VATSIM so the platform can keep user records current.
                    </p>
                    <p>Depending on how the deployment is configured, data may also be sent to:</p>
                    <ul>
                        <li>Email providers used to deliver notifications.</li>
                        <li>Discord webhooks used for operational alerts.</li>
                        <li>Sentry or similar error monitoring services for exception reporting.</li>
                        <li>A telemetry endpoint used to report instance-level metadata such as the application URL, owner name, version, environment, and a deterministic instance identifier.</li>
                    </ul>
                    <p>
                        The telemetry implementation does not intentionally send user identities. In this repository,
                        Sentry is configured with send_default_pii disabled.
                    </p>
                    <p>We do not sell personal data.</p>

                    <h4 class="mt-4">Cookies and Local Data</h4>
                    <p>
                        Control Center uses essential cookies and session data to keep users signed in and protect requests.
                        It may also use remember-me and CSRF-related cookies provided by the framework.
                    </p>
                    <p>
                        Any additional tracking depends on deployment configuration. If a deployment enables extra analytics or tracking scripts, those should be documented separately by the operator.
                    </p>

                    <h4 class="mt-4">Retention and Deletion</h4>
                    <p>
                        We keep personal data only as long as it is needed for administration, training history, auditing, troubleshooting, or legal and operational requirements.
                    </p>
                    <p>Some data has built-in retention behavior:</p>
                    <ul>
                        <li>Workmail addresses expire automatically after 60 days unless renewed.</li>
                        <li>User deletion can be handled either by pseudonymization or permanent deletion, depending on the request and administrative need.</li>
                        <li>Files can remain stored even if the uploading user is deleted; the uploader reference is cleared rather than the file being removed automatically.</li>
                    </ul>
                    <p>
                        When a user is pseudonymized, the deployment removes or nulls direct identifiers where possible so operational records remain usable without exposing the original identity.
                    </p>

                    <h4 class="mt-4">Your Rights</h4>
                    <p>
                        Depending on your local law, you may have the right to request access to your data, correction of inaccurate data, deletion, restriction of processing, or a copy of the data we hold about you.
                    </p>
                    <p>
                        If you want to exercise those rights, contact the administrators of the Control Center instance you use.
                    </p>

                    <h4 class="mt-4">Security</h4>
                    <p>
                        We use technical and organizational measures intended to protect personal data, including access controls and the normal security features provided by the framework and hosting environment.
                        No system can be guaranteed to be completely secure, so we recommend using strong credentials and reporting suspected account compromise quickly.
                    </p>

                    <h4 class="mt-4">Changes To This Policy</h4>
                    <p>
                        We may update this policy when the system changes or when processing practices change.
                        The current version should always reflect how the deployed instance actually handles data.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection