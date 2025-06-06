@foreach ($apps as $a)
    <tr class="hover:bg-gray-50">
        <td class="px-4 py-2 text-sm">{{ $a->app_created_at }}</td>
        <td class="px-4 py-2 text-sm">{{ $a->app_status }}</td>
        <td class="px-4 py-2 text-sm">{{ $a->app_id }}</td>
        <td class="px-4 py-2 text-sm">{{ $a->app_meta_give0 }}</td>
        <td class="px-4 py-2 text-sm">{{ rtrim(rtrim($a->app_sum1dc,'0'),'.') }}</td>
    </tr>
@endforeach
