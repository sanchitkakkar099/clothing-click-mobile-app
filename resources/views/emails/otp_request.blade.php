@extends('emails.layout.layout')
@section('content')
	<tr>
		<td align="left" valign="middle" style="font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:24px; color:#000000;">Dear {{$user->name}},</td>
	</tr>
	 <tr>
		<td style="line-height:15px;">&nbsp;</td>
	</tr>
	<tr>
		<td align="left" valign="middle" style="font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:24px; ">You are receiving this email because we received a One Time Password request for your account.</td>
	</tr>
	<tr>
		<td style="line-height:15px;">&nbsp;</td>
	</tr>
	<tr>
		<td align="left" valign="middle" style="font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:24px; ">Your One Time Password is: {{$otp}}</td>
	</tr>
	<tr>
		<td style="line-height:15px;">&nbsp;</td>
	</tr>
	<tr>
		<td align="left" valign="middle" style="font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:24px; ">If you did not request a One Time Password, no further action is required.</td>
	</tr>
@endsection