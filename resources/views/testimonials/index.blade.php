@extends('layouts.app')

@section('title', 'Testimonials | The Commission Apparel')

@section('content')
<section class="pt-32 pb-16 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-6">
        <div class="max-w-3xl">
            <p class="text-xs font-black uppercase tracking-widest text-secondary mb-3">Client Success</p>
            <h1 class="text-4xl md:text-5xl font-black tracking-tight uppercase text-slate-900">Testimonials</h1>
            <p class="text-slate-600 mt-4 text-base md:text-lg">
                See what teams, coaches, and athletes are saying about The Commission Apparel experience.
            </p>
        </div>
    </div>
</section>

<section class="py-12 lg:py-20 bg-slate-50 min-h-[50vh]">
    <div class="max-w-7xl mx-auto px-6">
        @if($testimonials->isEmpty())
            <div class="bg-white border-2 border-dashed border-slate-300 rounded-xl p-16 text-center text-slate-500 font-bold uppercase tracking-widest text-sm shadow-sm">
                No testimonials have been added yet.
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 items-stretch">
                @foreach($testimonials as $testimonial)
                    <article class="bg-white border border-slate-200 rounded-xl p-8 shadow-sm flex flex-col justify-between hover:shadow-lg transition-shadow duration-300">
                        <div class="mb-8">
                            <svg class="w-8 h-8 text-secondary/40 mb-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                            </svg>
                            <p class="text-black text-sm md:text-base font-medium leading-relaxed italic" style="color: #000000;">
                                "{{ $testimonial->content }}"
                            </p>
                        </div>
                        <div class="flex items-center gap-4 mt-auto pt-6 border-t border-slate-100">
                            @if($testimonial->image_path)
                                <img src="{{ $testimonial->image_path }}" alt="{{ $testimonial->client_name }}" class="w-12 h-12 rounded-full object-cover border border-slate-200 shrink-0 shadow-sm">
                            @else
                                <div class="w-12 h-12 rounded-full bg-[#1e40af] flex items-center justify-center shrink-0 shadow-sm text-white font-black text-lg">
                                    {{ substr($testimonial->client_name, 0, 1) }}
                                </div>
                            @endif
                            <div class="border-l-[3px] border-secondary pl-4">
                                <h3 class="font-black text-slate-900 text-sm uppercase tracking-wide">{{ $testimonial->client_name }}</h3>
                                @if($testimonial->organization)
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mt-0.5">{{ $testimonial->organization }}</p>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
        
        <div class="mt-16 text-center">
            <a href="{{ route('quote.show') }}" class="btn btn-primary px-8 py-4 text-base font-black uppercase tracking-wider rounded-md shadow-md transition-all">Start Your Custom Order</a>
        </div>
    </div>
</section>
@endsection
