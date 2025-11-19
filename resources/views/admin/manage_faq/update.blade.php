@extends('admin.layouts.app')
@section('content')
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <form class="forms-sample" method="POST" action="{{route('adminFaqUpdate')}}" id="userUpdate"> 
                    @csrf
                    <div class="form-group">
                        <label for="name">Question</label>
                        <input type="text" class="form-control" name="question" value="{{$user->question}}" placeholder="Question" required />
                        @error('question')
                        <span class="invalid-feedback" role="alert" style="display:block;">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
					<div class="form-group">
                        <label for="name">Answer</label>
						<textarea class="form-control" name="answer" placeholder="answer" required>{{$user->answer}}</textarea>
                       
                        @error('answer')
                        <span class="invalid-feedback" role="alert" style="display:block;">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <input type="hidden" name="uid" value="{{$user->id}}" />
                    <button type="submit" class="btn btn-gradient-primary mr-2">Update</button> 
                    <button type="reset" class="btn btn-light">Reset</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection