<div class="relative mt-4">
          <div class="overflow-x-auto">
              <div class="max-h-[600px] overflow-y-auto relative border border-gray-200 rounded-lg"> {{-- Tambah border dan rounded agar mirip card --}}
                  <table class="min-w-full divide-y divide-gray-200 text-xs table-auto"> {{-- Tambah class table-auto --}}
                      <thead class="bg-gray-50 sticky top-0 z-10">
                          <tr>
                              @foreach ($headers as $header)
                                  <th scope="col" class="px-6 py-3 text-left font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap bg-gray-50 {{ $header['class'] ?? '' }}">
                                      {{ $header['label'] }}
                                  </th>
                              @endforeach
                          </tr>
                      </thead>
                      <tbody class="bg-white divide-y divide-gray-200">
                          @foreach ($datas as $data)
                              <tr>
                                  <td class="px-6 py-4 whitespace-nowrap">{{ $data->name }}</td>
                                  <td class="px-6 py-4 whitespace-nowrap">{{ $data->area_name }}</td>
                                  <td class="px-6 py-4 whitespace-nowrap">{{ $data->point }}</td>

  
                                  <td class="px-6 py-4 whitespace-nowrap">
                                      Rp. {{ number_format($data->total_budget ?? 0, 0, ',', '.')}}
                                  </td>
                                  <td class="px-6 py-4 whitespace-nowrap">
                                      Rp. {{ number_format($data->total_spending ?? 0, 0, ',', '.')}}
                                  </td>
                                  <td class="px-6 py-4 whitespace-nowrap">
                                      Rp. {{ number_format($data->remaining_budget ?? 0, 0, ',', '.')}}
                                  </td>
                              </tr>
                          @endforeach
                          @if ($datas->isEmpty())
                              <tr>
                                  <td colspan="{{ count($headers) }}" class="px-6 py-4 text-center text-gray-500">
                                      {{ $textNoData }}
                                  </td>
                              </tr>
                          @endif
                      </tbody>
                      {{-- <tfoot class="sticky bottom-0">
                          <tr>
                              <th colspan="2" class="px-6 py-3 text-right font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap bg-gray-50">
                                  Total
                              </th>
                              <th class="px-6 py-3 text-left font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap bg-gray-50">
                                  {{ number_format($datas->sum('point') ?? 0, 0, ',', '.') }}
                              </th>
                              @foreach ($categories as $category)
                                  <th class="px-6 py-3 text-left font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap bg-gray-50">
                                      Rp.{{ number_format($datas->sum(function($data) use ($category) {
                                          return $data->budget_details->where('name', $category['name'])->first()['spending'] ?? 0;
                                      }), 0, ',', '.') }} /
                                      <br>
                                      Rp.{{ number_format($datas->sum(function($data) use ($category) {
                                          return $data->budget_details->where('name', $category['name'])->first()['amount'] ?? 0;
                                      }), 0, ',', '.') }}
                                  </th>
                              @endforeach
                              <th class="px-6 py-3 text-left font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap bg-gray-50">
                                  Rp. {{ number_format($datas->sum('total_budget') ?? 0, 0, ',', '.') }}
                              </th>
                              <th class="px-6 py-3 text-left font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap bg-gray-50">
                                  Rp. {{ number_format($datas->sum('total_spending') ?? 0, 0, ',', '.') }}
                              </th>
                              <th class="px-6 py-3 text-left font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap bg-gray-50">
                                  Rp. {{ number_format($datas->sum('remaining_budget') ?? 0, 0, ',', '.') }}
                              </th>
                          </tr>
                      </tfoot> --}}
                  </table>
              </div>
          </div>
      </div>