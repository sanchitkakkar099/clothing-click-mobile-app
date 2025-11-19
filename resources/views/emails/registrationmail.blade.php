@extends('emails.layout.layout')
@section('content')
	<tr>
		<td align="left" valign="middle" style="font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:24px; color:#000000;">Dear {{$name}},</td>
	</tr>
	 <tr>
		<td style="line-height:15px;">&nbsp;</td>
	</tr>
	<tr>
		<td align="left" valign="middle" style="font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:24px; ">A new person has registration, below are the details of that person</td>
	</tr>
	<tr>
		<td style="line-height:15px;">&nbsp;</td>
	</tr>
	<tr>
		<td align="left" valign="middle" style="font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:24px; ">Name: {{$name}}</td>
	</tr>
        <tr>
		<td align="left" valign="middle" style="font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:24px; ">Email: <a href="mailto:{{$email}}">{{$email}}</a></td>
	</tr>
         <tr>
		<td align="left" valign="middle" style="font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:24px; ">Password: {{$password}}</td>
	</tr>
        <tr>
		<td align="left" valign="middle" style="font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:24px; ">Phone: <a href="tel:{{$phone}}">{{$phone}}</a></td>
	</tr>
        <tr>
		<td align="left" valign="middle" style="font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:24px; ">Type: {{$type}}</td>
	</tr>
	<tr>
		<td style="line-height:15px;">&nbsp;</td>
	</tr>
	<tr>
		<td align="left" valign="middle" style="font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:24px; ">If you did not request a One Time Password, no further action is required.</td>
	</tr>
@endsection