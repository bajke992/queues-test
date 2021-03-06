@extends('layouts.app')

@section('content')
    <table>
        <thead style="background: white;">
            <th>
                Match count
            </th>
            @foreach($results[0]->odds as $odd)
                <th>
                    {{ $odd->name }}
                </th>
            @endforeach
        </thead>
        <tbody>
            @foreach($results as $result)
                <tr>
                    <td>
                        {{ $result->count }}
                    </td>
                    @foreach($result->odds as $odd)
                        <td>
                            {{ $odd->value }}
                            |
                            @if($odd->win_count > 0)
                                <b style="color: red;">
                            @endif
                            {{ number_format($odd->win_count / $result->count * 100, 0) }}%
                            {{--{{ $odd->win_count }}--}}
                                    @if($odd->win_count > 0)
                                </b>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

@endsection

@section('js')
    <script>
        $(document).ready(function() {

            $table = $('table');
            $table.floatThead();
        });
    </script>
@endsection