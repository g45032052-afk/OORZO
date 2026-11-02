   <div>
       <div class="custom-offcanvas-header d-flex justify-content-between align-items-center">
           <div class="px-3 py-3 d-flex justify-content-between w-100">
               <div class="d-flex flex-wrap align-items-center gap-2">
                   <h2 class="mb-0 fs-18 text-title font-medium">{{ translate('Saved Address') }}</h2>
                    
               </div>
               <button type="button"
                   class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                   aria-label="Close">&times;
               </button>
           </div>
       </div>
       <div class="custom-offcanvas-body p-20">
           <div class="d-flex flex-column gap-20px pb-5">


               @dump($customer->addresses)
               @forelse ($customer->addresses ?? [] as $address)
                   <div class="global-bg-box p-10px rounded">
                       <div class="d-flex align-items-cetner justify-content-between gap-2 flex-wrap mb-10px">
                           <h5 class="text-title m-0 text-capitalize">
                               {{ $address->address_type }}
                               {{-- <span class="gray-dark">(Shipping Address)</span> --}}
                           </h5>
                           <button type="button" class="btn p-0 bg-transparent text-primary" data-toggle="modal"
                               data-target="#addressEdit__modal">
                               <i class="tio-edit"></i>
                           </button>
                       </div>
                       <div class="bg-white rounded p-10px d-flex flex-column gap-1">
                           <div class="d-flex gap-2">
                               <span class="before-info w-90px min-w-90 gray-dark fs-12">
                                   {{ translate('Name') }}
                               </span>
                               <span class="fs-12 text-title">
                                   {{ $address->contact_person_name }}
                                   ({{ $address->contact_person_number }})
                               </span>
                           </div>
                           @if ($address->emai)
                               <div class="d-flex gap-2">
                                   <span class="before-info w-90px min-w-90 gray-dark fs-12">
                                       {{ translate('Email') }}
                                   </span>
                                   <span class="fs-12 text-title">
                                       {{ $address->email }}
                                   </span>
                               </div>
                           @endif
                           <div class="d-flex gap-2">
                               <span class="before-info w-90px min-w-90 gray-dark fs-12">
                                   {{ translate('Address Type') }}
                               </span>
                               <span class="fs-12 text-title">
                                   {{ $address->address_type }}
                               </span>
                           </div>


                           @if ($address->floor)
                               <div class="d-flex gap-2">
                                   <span class="before-info w-90px min-w-90 gray-dark fs-12">
                                       {{ translate('Floor') }}
                                   </span>
                                   <span class="fs-12 text-title">
                                       {{ $address->floor }}
                                   </span>
                               </div>
                           @endif


                           @if ($address->house)
                               <div class="d-flex gap-2">
                                   <span class="before-info w-90px min-w-90 gray-dark fs-12">
                                       {{ translate('house') }}
                                   </span>
                                   <span class="fs-12 text-title">
                                       {{ $address->house }}
                                   </span>
                               </div>
                           @endif

                           @if ($address->road)
                               <div class="d-flex gap-2">
                                   <span class="before-info w-90px min-w-90 gray-dark fs-12">
                                       {{ translate('Road') }}
                                   </span>
                                   <span class="fs-12 text-title">
                                       {{ $address->road }}
                                   </span>
                               </div>
                           @endif
                           <div class="d-flex gap-2">
                               <span class="before-info align-items-start w-90px min-w-90 gray-dark fs-12">
                                   {{ translate('Address') }}
                               </span>
                               <span class="fs-12 text-title">
                                   @if ($address->latitude && $address->longitude)
                                       <a href="https://www.google.com/maps/search/?api=1&query={{ $address->latitude }},{{ $address->longitude }}"
                                           target="_blank" class="fs-12 text-title">
                                           {{ $address->address }} </a>
                                   @else
                                       {{ $address->address }}
                                   @endif


                               </span>
                           </div>
                       </div>
                   </div>

               @empty
               @endforelse

           </div>
       </div>
   </div>
   <div
       class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center offcanvas-footer p-3 position-sticky">
       <button type="submit" class="btn w-100 btn--primary">{{ translate('Add New Address') }}</button>
   </div>
