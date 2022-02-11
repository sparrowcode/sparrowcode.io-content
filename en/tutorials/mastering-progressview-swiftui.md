To indicate the background work in the application use `ProgressView`.

## Indeterminate progress

Let's add a `ProgressView()`:

```swift
struct ContentView: View {
    
    var body: some View {
        VStack(spacing: 40) {
            ProgressView()
            Divider()
            ProgressView("Loading")
                .tint(.pink)
        }
    }
}
```

[Indeterminate Activity Indicator](https://cdn.ivanvorobei.by/websites/sparrowcode.io/mastering-progressview-swiftui/indeterminate_activity_indicator.mov)

By default `SwiftUI` defines a rotating loading bar (spinner). The modifier `.tint()` changes the color of the bar.

## Determinate progress

Initialize the view with another indicator:

```swift
struct ContentView: View {
    
    let totalProgress: Double = 100
    @State private var progress = 0.0
    
    var body: some View {
        VStack(spacing: 40) {
            currentTextProgress
            
            ProgressView(value: progress, total: totalProgress)
                .padding(.horizontal, 40)
            
            loadResetButtons
        }
    }
}

extension ContentView {

    private var currentTextProgress: Text {
        switch progress {
        case 5..<totalProgress: return Text("Current progress: \(Int(progress))%")
        case totalProgress...: return Text("Loading complete")
        default: return Text("Start loading")
        }
    }
    
    private var loadResetButtons: some View {
        HStack(spacing: 20) {
            Button("Load more") {
                withAnimation { progress += Double.random(in: 5...20) }
            }
            .disabled(!progress.isLessThanOrEqualTo(totalProgress))
            
            Button(role: .destructive) {
                progress = 0
            } label: {
                Text("Reset")
            }
            .tint(.red)
            .disabled(progress.isZero)
        }
        .buttonStyle(.bordered)
    }
}
```

[Determinate Activity Indicator](https://cdn.ivanvorobei.by/websites/sparrowcode.io/mastering-progressview-swiftui/determinate_activity_indicator.mov)

Pressing the `Load more` button starts the download. The text shows the current progress and the `Reset` button will become available to tap and reset. When the download is finished, the text on the screen will let you know. The `Load more` button will become inactive.

Let's make a progress simulation with a timer:

```swift
// filename: TimerProgressView.swift

struct TimerProgressView: View {
    
    let timer = Timer
        .publish(every: 0.05, on: .main, in: .common)
        .autoconnect()
    
    let downloadTotal: Double = 100
    @State private var progress: Double = 0
    
    var body: some View {
        VStack(spacing: 40) {
            Text("Downloading: \(Int(progress))%")
            
            ProgressView(value: progress, total: downloadTotal)
                .tint(progress < 50 ? .pink : .green)
                .padding(.horizontal)
                .onReceive(timer) { _ in
                    if progress < downloadTotal { progress += 1 }
                }
        }
    }
}
```

[Timer Progress](https://cdn.ivanvorobei.by/websites/sparrowcode.io/mastering-progressview-swiftui/timer_progress.mov)

The event is called several times by a timer. Timer source code:

```swift
let timer = Timer.publish(every: 0.05, on: .main, in: .common).autoconnect()
```

The timer is triggered every 0.05 seconds (50 milliseconds). The timer must run in the main thread and the common run loop. The run loop allows code to be processed when the user does something (presses a button). The timer starts counting down instantly.

When `progress` reaches the `downloadTotal` value, the timer stops.
When it reaches 50% of the download, the indicator changes color into green.

The `ProgressView` looks like a loading bar that fills from left to right.
This is how we show the user that the loading progress depends on the size of the file.

A description of the `publish` method is available in [Apple documentation](https://developer.apple.com/documentation/foundation/timer/3329589-publish). More initializers can be found in the Xcode documentation or on the [website](https://developer.apple.com/documentation/swiftui/progressview).

![Documentation SwiftUI ProgressView](https://cdn.ivanvorobei.by/websites/sparrowcode.io/mastering-progressview-swiftui/progressview_init.png)

## Styling Progress Views

A custom design for `ProgressView` is created using the protocol `ProgressViewStyle`, which we need to inherit from it. Let's declare a structure `RoundedProgressViewStyle` which contains method `makeBody()` and takes configuration parameter for the style:

```swift
struct RoundedProgressViewStyle: ProgressViewStyle {
    
    let color: Color
    
    func makeBody(configuration: Configuration) -> some View {
        let fractionCompleted = configuration.fractionCompleted ?? 0
        
        RoundedRectangle(cornerRadius: 18)
            .frame(width: CGFloat(fractionCompleted) * 200, height: 22)
            .foregroundColor(color)
            .padding(.horizontal)
    }
}
```

Let's go back to `TimerProgressView.swift` and pass `RoundedProgressViewStyle(color: .cyan)` to the `.progressViewStyle()` modifier. Now the code looks like this:

```swift
struct TimerProgressView: View {
    
    let timer = Timer
        .publish(every: 0.05, on: .main, in: .common)
        .autoconnect()
    
    let downloadTotal: Double = 100
    @State private var progress: Double = 0
    
    var body: some View {
        VStack(spacing: 40) {
            Text("Downloading: \(Int(progress))%")
            
            ProgressView(value: progress, total: downloadTotal)
                .onReceive(timer) { _ in
                    if progress < downloadTotal { progress += 1 }
                }
                .progressViewStyle(
                    RoundedProgressViewStyle(color: .cyan)
                )
        }
    }
}
```

Progress begins not from left to right, but from the middle in opposite directions.

[RoundedProgressViewStyle](https://cdn.ivanvorobei.by/websites/sparrowcode.io/mastering-progressview-swiftui/rounded_progress_view.mov)
