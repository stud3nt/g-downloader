export class AppHelper {

    public static randomNumber(min: number = 1, max: number = 10): number {
        return Math.floor(Math.random() * (max - min + 1) + min);
    }

}
