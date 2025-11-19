@extends('emails.layout.layout')
@section('content')
<tr>
	<td align="center" valign="top" bgcolor="#fff" style="padding:20px 0;">
		<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
			<tbody>
				<tr>
					<td align="left" valign="middle" style="font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:24px; color:#000000;">Dear user,</td>
				</tr>
				 <tr>
					<td style="line-height:15px;">&nbsp;</td>
				</tr>
				<tr>
					<td align="left" valign="middle" style="font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:24px; ">Adarsh has reviewed Tohfa. Please find the details of the reviewer below.</td>
				</tr>
			
				<tr>
					<td align="left" valign="middle" style="padding:15px 0;"><table width="100%" border="0" cellspacing="0" cellpadding="0">
							<tbody>
								<tr>
									<td align="left" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:24px; width: 100px;">Book Name :</td>
									<td align="left" valign="middle" style="font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:24px; ">aaa</td>
								</tr>
								<tr>
									<td align="left" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:24px; width: 100px;">User Name :</td>
									<td align="left" valign="middle" style="font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:24px; ">nbbbb</td>
								</tr>
								<tr>
									<td align="left" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:24px; width: 100px;">Email ID :</td>
									<td align="left" valign="middle" style="font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:24px; ">aaaa</td>
								</tr>
								<tr>
									<td align="left" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:24px;width: 100px;">Review :</td>
									<td align="left" valign="middle" style="font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:24px; ">aaaaaaaaa</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr>
					<td style="line-height:15px;">&nbsp;</td>
				</tr>
				<tr>
					<td align="left" valign="middle"><a href="aaa" title="aaa" target="_blank" style="font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:20px; font-weight:500; color:#ffffff; text-decoration:none; padding:8px 30px; border-radius:5px; background-color:#89170c;">aaaa</a></td>
				</tr>
				
				<tr>
					<td height="30" align="left" valign="middle">&nbsp;</td>
				</tr>
				<tr>
					<td align="left" valign="middle" style="font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:18px; font-weight:600; color:#000000;">Best Regards,</td>
				</tr>
				<tr>
					<td align="left" valign="middle"><a href="{{ url('/') }}" target="_blank" style="font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:16px; font-weight:400; color:#000;  text-decoration:none;">{{config('constants.SITE_NAME')}}</a></td>
				</tr>
			</tbody>
		</table>
	</td>
</tr>
@endsection