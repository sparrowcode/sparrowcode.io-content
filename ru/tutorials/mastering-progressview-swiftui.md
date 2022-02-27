Чтобы обозначить фоновую работу в приложении используют `ProgressView`.

## Неопределенный прогресс

Добавим `ProgressView()`:

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

[Indeterminate Activity Indicator](https://cdn.ivanvorobei.io/websites/sparrowcode.io/mastering-progressview-swiftui/indeterminate_activity_indicator.mov)

По умолчанию `SwiftUI` определяет вращающийся бар загрузки (спиннер). Модификатор `.tint()` меняет цвет бара.

## Определенный прогресс

Используем явный индикатор - инициализируем вью:

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
```

И добавим в экстеншен:

```swift
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

[Determinate Activity Indicator](https://cdn.ivanvorobei.io/websites/sparrowcode.io/mastering-progressview-swiftui/determinate_activity_indicator.mov)

По нажатию на `Load more` начинается загрузка. Текст показывает прогресс, а кнопка `Reset` для сброса. Текст на экране изменится, когда загрузка закончится. Кнопка `Load more` станет неактивной.

Сделаем симуляцию прогресса c таймером:

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

[Timer Progress](https://cdn.ivanvorobei.io/websites/sparrowcode.io/mastering-progressview-swiftui/timer_progress.mov)

Событие вызывается несколько раз при помощи таймера. Код:

```swift
let timer = Timer.publish(every: 0.05, on: .main, in: .common).autoconnect()
```

Таймер срабатывает каждые 0.05 секунд (50 миллисекунд), он должен работать в главном потоке и общем цикле `common run loop`. Run loop позволяет обрабатывать код, когда пользователь взаимодействует и интерфейсом. Таймер начнет отсчитывать время моментально.

Когда `progress` достигнет `downloadTotal` значения, таймер остановится.
При достижении 50% загрузки, индикатор меняет цвет на зеленый.

`ProgressView` это полоса загрузки, заполняется слева направо.

Описание метода `publish` доступно в [документации Apple](https://developer.apple.com/documentation/foundation/timer/3329589-publish). Больше инициализаторов в документации Xcode или [на сайте](https://developer.apple.com/documentation/swiftui/progressview).

![Documentation SwiftUI ProgressView](https://cdn.ivanvorobei.io/websites/sparrowcode.io/mastering-progressview-swiftui/progressview_init.png)

## Дизайн

Чтобы создать кастомный дизайн для `ProgressView`, нужно наследоваться от протокола `ProgressViewStyle`. Объявим структуру `RoundedProgressViewStyle` c методом `makeBody()` и принимающим параметр конфигурации для стиля:

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

Передадим `RoundedProgressViewStyle(color: .cyan)` в модификатор `.progressViewStyle()`:

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

Теперь прогресс продолжается с середины в противоположные стороны:

[RoundedProgressViewStyle](https://cdn.ivanvorobei.io/websites/sparrowcode.io/mastering-progressview-swiftui/rounded_progress_view.mov)
