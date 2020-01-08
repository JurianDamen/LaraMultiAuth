@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Setup Two Factor Authentication') }}</div>

                    <div class="card-body">
                        @if (isset($alreadySetup) && $alreadySetup)
                            <div>
                                {{ __('Two Factor Authenticatio is setup for you!')  }}
                            </div>

                            <form method="POST" action="{{ route('laramultiauth.setup.disable') }}">
                                @csrf

                                <div class="form-group row mb-0">
                                    <div class="col-md-2 offset-md-10">
                                        <button type="submit" class="btn btn-danger">
                                            {{ __('Disable') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @else
                            <div>
                                @if (!empty($qrCode))
                                    <img src="{{ $qrCode }}">
                                @endif
                                <span>Key: {{ $token }}</span>
                            </div>

                            <form method="POST" action="{{ route('laramultiauth.setup.post') }}">
                                @csrf

                                <input id="secret" type="hidden" value="{{ $token }}" name="secret">

                                <div class="form-group row">
                                    <label for="token"
                                           class="col-md-4 col-form-label text-md-right">{{ __('Code') }}</label>

                                    <div class="col-md-6">
                                        <input id="token" type="text"
                                               class="form-control @error('token') is-invalid @enderror" name="token"
                                               required autofocus>

                                        @error('token')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row mb-0">
                                    <div class="col-md-8 offset-md-4">
                                        <button type="submit" class="btn btn-primary">
                                            {{ __('Verify and Setup') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
