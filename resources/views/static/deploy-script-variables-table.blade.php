<table class="deploy-script-variables w-full text-sm">
    <thead>
    <tr>
        <th width="34%">Variable</th>
        <th width="66%">Output</th>
    </tr>
    </thead>

    <tbody>
        @foreach($rows as $variable => $output)
        <tr>
            <td>
                <code>{{ $variable }}</code>
            </td>

            <td>
                {!! $output !!}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
