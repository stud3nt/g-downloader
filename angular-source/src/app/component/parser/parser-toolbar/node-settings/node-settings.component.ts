import {Component, EventEmitter, Input, OnDestroy, OnInit, Output} from '@angular/core';
import {ParserNodeSettings} from "../../../../model/parser-node-settings";
import {FilesHelper} from "../../../../helper/files-helper";
import {PrefixSufixType} from "../../../../enum/prefix-sufix-type";
import {FolderType} from "../../../../enum/folder-type";
import {AppHelper} from "../../../../helper/app-helper";
import {ModalType} from "../../../../enum/modal-type";
import {ModalSize} from "../../../../enum/modal-size";
import {ModalService} from "../../../../service/modal.service";
import {Observable} from "rxjs";
import {ParserRequest} from "../../../../model/parser-request";

@Component({
    selector: 'node-settings',
    templateUrl: './node-settings.component.html',
    styleUrls: ['./node-settings.component.scss']
})
export class NodeSettingsComponent implements OnInit, OnDestroy {

    public ModalType = ModalType;
    public ModalSize = ModalSize;

    public _settingsModalId: string = 'node-settings-modal';

    public filesHelper = new FilesHelper();
    public PrefixSufixType = PrefixSufixType;
    public FolderType = FolderType;

    public prefixes: {name: string, type: string}[] = [];
    public folders: {name: string, type: string}[] = [];

    public sizeUnit: string = '';

    public prefixLabel: string = '';
    public sufixLabel: string = '';
    public folderLabel: string = '';

    public _nodeSettings: ParserNodeSettings = null;
    private _tmpNodeSettings: ParserNodeSettings = null;
    private _parserRequest: ParserRequest = null;

    @Input() set parserRequest(parserRequest: ParserRequest) {
        let settings = parserRequest.currentNode.settings;

        this._nodeSettings = settings;
        this._tmpNodeSettings = settings;
        this._parserRequest = parserRequest;

        if (settings.maxSize > 0)
            for (let unit of this.filesHelper.filesSizesUnit)
                if (unit.symbol === this._nodeSettings.sizeUnit)
                    this.sizeUnit = unit.name;

        for (let prefix of this.prefixes)
            if (settings.prefixType === prefix.type)
                this.prefixLabel = prefix.name;

        for (let sufix of this.prefixes)
            if (settings.sufixType === sufix.type)
                this.sufixLabel = sufix.name;

        for (let folder of this.folders)
            if (settings.folderType === folder.type)
                this.folderLabel = folder.name;

        for (let unit of this.filesHelper.filesSizesUnit)
            if (settings.sizeUnit === unit.symbol)
                this.sizeUnit = unit.name;
    }

    @Input() events: Observable<void>;

    @Output() onSettingsChange = new EventEmitter<ParserNodeSettings>();

    @Output() onSettingsClose = new EventEmitter<number>();

    constructor(
        private modalService: ModalService
    ) {
        this.prefixes = PrefixSufixType.getIterableData();
        this.folders = FolderType.getIterableData();
    }

    ngOnInit() {
        this.events.subscribe(() => {
            this.modalService.selectModal(this._settingsModalId).open()
        });
    }

    ngOnDestroy(): void {
        this.modalService.remove(this._settingsModalId);
    }

    public resetSettings(): void {
        this._nodeSettings = this._tmpNodeSettings;
    }

    public closeSettings(): void {
        this.onSettingsClose.next(
            AppHelper.randomNumber(1, 10000)
        );
    }

    public saveSettings(): void {
        this.onSettingsChange.next(this._nodeSettings);
    }

}
